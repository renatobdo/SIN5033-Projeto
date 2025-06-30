from fastapi import FastAPI, Query, HTTPException
from pydantic import BaseModel
from fastapi.middleware.cors import CORSMiddleware
from SPARQLWrapper import SPARQLWrapper, JSON
from datetime import datetime, date
from typing import List, Dict, Any

app = FastAPI(title="API de Recomendações - Arboviroses")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"], allow_credentials=True,
    allow_methods=["*"], allow_headers=["*"]
)

FUSEKI_QUERY = "http://localhost:3030/arboviroses/query"
FUSEKI_UPDATE = "http://localhost:3030/arboviroses/update"
NS = "http://www.exemplo.org/arboviroses#"
PREFIX = f"PREFIX : <{NS}>\n"

def _run_sparql(query: str, endpoint: str = FUSEKI_QUERY) -> List[Dict[str, Any]]:
    sparql = SPARQLWrapper(endpoint)
    sparql.setQuery(PREFIX + query)
    sparql.setReturnFormat(JSON)
    sparql.setMethod("POST")
    return sparql.query().convert()["results"]["bindings"]

def _get_preferencias(user_uri: str) -> list[str]:
    q = f"""
    SELECT ?tipo WHERE {{
        {user_uri} :temPreferenciaTipo ?tipo .
    }}
    """
    rs = _run_sparql(q)
    return [r["tipo"]["value"] for r in rs]

@app.get("/api/recomendacoes")
def recomendar(user: str = Query(...), dtnascimento: str = Query(...)):
    user_uri = f":{user.lower()}"
    preferencias = _get_preferencias(user_uri)

    try:
        dob = datetime.strptime(dtnascimento, "%Y-%m-%d").date()
    except ValueError:
        raise HTTPException(400, detail="Data de nascimento inválida (use YYYY-MM-DD)")

    idade_dias = (date.today() - dob).days
    dias_10 = 365 * 10
    dias_14_11_29 = 365 * 14 + 30 * 11 + 29
    elegivel = dias_10 <= idade_dias <= dias_14_11_29

    msg_vacina = (
        "A vacina da dengue (atenuada) está indicada para crianças e adolescentes "
        "de 10 a 14 anos, 11 meses e 29 dias de idade. O esquema vacinal é de 2 doses "
        "com intervalo de 3 meses. <strong>Você pode ser vacinado.</strong>"
        if elegivel else
        "Você <strong>não</strong> se enquadra nos critérios de idade para vacinação "
        "contra dengue (válida para 10 – 14 anos, 11 meses e 29 dias)."
    )

#recomendação por conteúdo

    q_conteudo = f"""
    SELECT ?recurso ?tipo ?mediaNota WHERE {{
        {user_uri} :temPreferenciaTipo ?tipo .
        ?recurso a :RecursoEducacional ;
                 :temTipo ?tipo ;
                 :temMediaNota ?mediaNota .
    }} ORDER BY DESC(?mediaNota) LIMIT 5
    """
        
    # Recomendação colaborativa 
    q_colab = f"""
PREFIX : <http://www.exemplo.org/arboviroses#>

SELECT ?recurso ?tipo ?mediaNota (COUNT(DISTINCT ?outro) AS ?qtdAcessos) WHERE {{
  {user_uri} :temPreferenciaTipo ?tipo_comum .

  ?outro a :Usuario ;
          :temPreferenciaTipo ?tipo_comum ;
          :acessouRecurso ?recurso .

  FILTER (?outro != {user_uri})

  ?recurso a :RecursoEducacional ;
           :temTipo ?tipo ;
           :temMediaNota ?mediaNota .
}}
GROUP BY ?recurso ?tipo ?mediaNota
ORDER BY DESC(?mediaNota) DESC(?qtdAcessos)
LIMIT 5
"""






    q_semana = """
    SELECT ?semana ?dengue ?alarme ?grave WHERE {
      ?ind a :IndicadorEpidemiologico ;
           :weekNumber ?semana ;
           :casesDengue ?dengue ;
           :casesDengueAlarm ?alarme ;
           :casesDengueSevere ?grave .
    } ORDER BY DESC(?semana) LIMIT 1
    """

    res_videos = []
    if elegivel and "video" in [p.lower() for p in preferencias]:
        q_videos = """
        SELECT ?recurso ?titulo WHERE {
          ?recurso a :RecursoEducacional ;
                   :temTipo "video" ;
                   :temTema "vacina dengue" ;
                   :temTitulo ?titulo .
        } LIMIT 5
        """
        res_videos = _run_sparql(q_videos)

    res_conteudo = _run_sparql(q_conteudo)
    print(q_colab)
    res_colab    = _run_sparql(q_colab)
    res_semana   = _run_sparql(q_semana)

    
    def _parse(rs):
        return [
            {
                "recurso": r["recurso"]["value"],
                "tipo": r["tipo"]["value"],                
                "nota": round(float(r["mediaNota"]["value"]), 2),
                "qtd_acessos": int(r.get("qtdAcessos", {}).get("value", 0)),
                "nome_usuario": r.get("nome", {}).get("value"),
                "email_usuario": r.get("email", {}).get("value"),
                "idade_usuario": int(r.get("idade", {}).get("value")) if "idade" in r else None
            }
            for r in rs
        ]


    def _parse_videos(rs):
        return [{"titulo": v["titulo"]["value"], "url": v["recurso"]["value"]} for v in rs]

    semana_info = {}
    if res_semana:
        s = res_semana[0]
        semana_info = {
            "semana": int(s["semana"]["value"]),
            "dengue": int(s["dengue"]["value"]),
            "alarme": int(s["alarme"]["value"]),
            "grave":  int(s["grave"]["value"]),
        }

    return {
        "mensagem_vacina": msg_vacina,
        "elegivel_vacina": elegivel,
        "preferencias": preferencias,
        "videos_vacina": _parse_videos(res_videos),
        "semana": semana_info,
        "conteudo": _parse(res_conteudo),
        "colaborativa": _parse(res_colab)
    }

class QuizResposta(BaseModel):
    nome: str
    idade: int
    email: str
    sintomas: List[str]
    interesse: str
    tipo_recurso: str

@app.post("/quiz")
def processar_quiz(resposta: QuizResposta):
    nomeId = resposta.nome.replace(" ", "_").lower()
    triples = f":usuario{nomeId} a :Usuario ;\n" \
              f"  :temNome \"{resposta.nome}\" ;\n" \
              f"  :temIdade {resposta.idade} ;\n" \
              f"  :temEmail \"{resposta.email}\" ;\n" \
              f"  :temPreferenciaTipo \"{resposta.tipo_recurso}\" .\n"

    q_insert = PREFIX + "INSERT DATA {\n" + triples + "}"
    sparql = SPARQLWrapper(FUSEKI_UPDATE)
    sparql.setMethod("POST")
    sparql.setQuery(q_insert)
    sparql.query()

    return {"mensagem": f"Usuário {resposta.nome} inserido com sucesso!"}
