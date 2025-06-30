## SIN5033-Projeto

Projeto - Sistema de recomendação baseado em colaboração e em conteúdo

video explicativo: https://www.youtube.com/watch?v=leKJjDzIxtE

## Recursos

### Linguagens

- Java (JDK 24)
- Python

### Frameworks e Bibliotecas

- FastAPI (backend Python)
- Laravel (framework PHP)
- OWLReady2 (manipulação de ontologias em Python)

### Ferramentas Semânticas

- [Apache Jena Fuseki](https://jena.apache.org/download/) – servidor SPARQL
- Protégé – editor de ontologias OWL

### Banco de Dados

- PostgreSQL



![Arquitetura-Sistema-Recomendacao](https://github.com/user-attachments/assets/ec756454-50d6-4641-86d9-7102db087abb)



## Requisitos

![image](https://github.com/user-attachments/assets/5cd3fbc6-749f-4401-96c9-81d05dc2202d)

### subir o servidor
cd C:\testes\Ontologias
uvicorn main:app --reload

![image](https://github.com/user-attachments/assets/e82c7700-c1cc-4066-b039-4fe6fcc9f8bb)


Abra no navegador:

    http://localhost:8000 → mensagem de sucesso

    http://localhost:8000/recursos → lista os recursos educacionais (se existirem)

Também disponível em Swagger:

📘 http://localhost:8000/docs

### Apache Jena

http://localhost:3030


![image](https://github.com/user-attachments/assets/62c7a5b8-0ca9-4901-9f31-784dac14ac94)


### Laravel frontend
criação do projeto:
composer create-project laravel/laravel arboviroses-sparql-recommender

cd C:\xampp\htdocs\arboviroses-sparql-recommender

php artisan serve --port=8001

![image](https://github.com/user-attachments/assets/7c877765-0c78-48fd-ad38-beb816f80350)


Para forçar a recriação do banco de dado PostgreSQL

php artisan migrate:fresh --seed


## Sistema de recomendação
No código da API FastAPI, a recomendação foi implementada em duas estratégias distintas: por conteúdo e por colaboração. Ambas utilizam SPARQL diretamente no endpoint Fuseki. A seguir, explico como cada uma foi realizada:

✅ 1. Recomendação por Conteúdo

Objetivo: recomendar recursos educacionais que estejam alinhados com o tipo de recurso que o próprio usuário informou como preferência (ex: vídeo, cartilha, jogo).
SPARQL usado:
```
q_conteudo = f"""
    SELECT ?recurso ?tipo ?mediaNota WHERE {{
        {user_uri} :temPreferenciaTipo ?tipo .
        ?recurso a :RecursoEducacional ;
                 :temTipo ?tipo ;
                 :temMediaNota ?mediaNota .
    }} ORDER BY DESC(?mediaNota) LIMIT 5
    """
```
O que faz:

    Pega os tipos de preferência (:temPreferenciaTipo) associados ao usuário.

    Busca recursos (:RecursoEducacional) que tenham esse mesmo tipo (:temTipo).

    Ordena os resultados pela nota (:temNota), do maior para o menor.

    Limita a 5 recursos.

    Exemplo: Se o usuário gosta de "vídeo", o sistema traz os vídeos mais bem avaliados.

✅ 2. Recomendação por Colaboração

A consulta por recomendação colaborativa semântica tem como objetivo sugerir recursos educacionais a um usuário com base em preferências compartilhadas com outros usuários. A recomendação é realizada também pela quantidade de acessos a determinado recurso e pela maior nota média atribuída ao recurso
```
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
```

Por exemplo, o usuário Renato tem preferência por vídeo e podcast. Caso outros
usuários tenham pelo menos uma preferência em comum, o sistema de recomendação
retorna Recursos educacionais acessados pelos outros usuários ordenados pela média
de notas e quantidade de acessos.

![sistema_recomendacoes_dengue](https://github.com/user-attachments/assets/9776055b-72ec-4620-a7c2-2218ea67ae9f)

![sistema_recomendacoes_dengue2](https://github.com/user-attachments/assets/956effb3-17bb-49ec-b71a-6cec32486b2a)

## Consulta classes existentes

```sparql
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX owl: <http://www.w3.org/2002/07/owl#>
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>

SELECT DISTINCT ?classe WHERE {
  {
    ?classe rdf:type owl:Class .
  }
  UNION
  {
    ?classe rdf:type rdfs:Class .
  }
}
ORDER BY ?classe
```

## Consulta instâncias da classe usuário
```sparql
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX : <http://www.exemplo.org/arboviroses#>

SELECT DISTINCT ?usuario ?nome ?email ?dtnascimento WHERE {
  ?usuario rdf:type :Usuario .
  OPTIONAL { ?usuario :temNome ?nome . }
  OPTIONAL { ?usuario :temEmail ?email . }
  OPTIONAL { ?usuario :temDataNascimento ?dtnascimento . }
}
ORDER BY ?usuario
```


## Consulta recursos educacionais

```sparql
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX : <http://www.exemplo.org/arboviroses#>

SELECT DISTINCT ?recurso ?nota WHERE {
  ?recurso rdf:type :RecursoEducacional .
  OPTIONAL { ?recurso :temNota ?nota . }
}
ORDER BY ?recurso
```

## recursos com temtipo e temnota
```sparql
PREFIX : <http://www.exemplo.org/arboviroses#>
SELECT ?recurso ?tipo ?nota WHERE {
  ?recurso a :RecursoEducacional ;
           :temTipo ?tipo ;
           :temNota ?nota .
}
```

## Usuário possui preferência
```sparql
PREFIX : <http://www.exemplo.org/arboviroses#>
SELECT ?usuario ?tipo WHERE {
  ?usuario :temPreferenciaTipo ?tipo .
}
```
## Consulta Semanas epidemiológicas
```
PREFIX : <http://www.exemplo.org/arboviroses#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>

SELECT ?semana ?casos ?sinaisAlarme ?casosGraves ?inicio ?fim WHERE {
  ?semana a :IndicadorEpidemiologico ;
          :casesDengue ?casos ;
          :casesDengueAlarm ?sinaisAlarme ;
          :casesDengueSevere ?casosGraves ;
          :weekStart ?inicio ;
          :weekEnd ?fim .
}
ORDER BY DESC(?fim)
```
## Consulta média de notas
```
PREFIX : <http://www.exemplo.org/arboviroses#>

SELECT ?recurso ?media WHERE {
  ?recurso :temMediaNota ?media .
}
ORDER BY DESC(?media)
```

## Consulta média de notas por tipo, tema, título.
```
PREFIX : <http://www.exemplo.org/arboviroses#>

SELECT ?tipo ?tema ?titulo ?media WHERE {
  ?recurso a :RecursoEducacional ;
           :temTipo ?tipo ;
           :temTema ?tema ;
           :temTitulo ?titulo ;
           :temMediaNota ?media .
}
ORDER BY DESC(?media)
```
## Conultar todos os acessos a determinado recurso
```
PREFIX : <http://www.exemplo.org/arboviroses#>

SELECT ?usuario ?recurso WHERE {
  ?usuario :acessouRecurso ?recurso .
}
```

## Comandos para exportar os dados (cmd do windows) do apache jena fuseki para arquivo dados.ttl

curl -H "Accept: text/turtle" --data-urlencode "query=CONSTRUCT { ?s ?p ?o } WHERE { ?s ?p ?o }" http://localhost:3030/arboviroses/query -o dados.ttl

## Para dar flush no database do apache jena

Entre na pasta  C:\apache-jena-fuseki-5.4.0\run\databases e remova os databases

## Conclusões
Para trabalhos futuros pretende-se utilizar a região do usuário para realizar recomendações mais direcionadas. Por exemplo, caso um usuário seja do Ipiranga e lá tenha altos índices de dengue (300 casos por 100 mil habitantes) uma recomendação para epidemia e com base nas preferências do usuário (vídeo, por exemplo) serão disponibilizados. Os dados que serão coletados serão do SINAN, mas também da prefeitura de São Paulo, pois tem dados dos bairros.

## Referências

https://portalsinan.saude.gov.br/dados-epidemiologicos-sinan

http://tabnet.datasus.gov.br/cgi/deftohtm.exe?sinannet/cnv/denguebsp.def

https://prefeitura.sp.gov.br/documents/d/saude/documento-tecnico_-dengue_2025-pdf

https://docs.google.com/spreadsheets/d/1a46T0khfDvURwdXQ6CAovSgufJffJBhcW6mmBxSomUI/edit?usp=sharing 

https://nies.saude.sp.gov.br/ses/publico/dengue 

https://portal.saude.sp.gov.br/resources/cve-centro-de-vigilancia-epidemiologica/areas-de-vigilancia/doencas-de-transmissao-por-vetores-e-zoonoses/doc/dengue/planodecontingeencia_arbo_2025_2026_sp_final-coupia.pdf

https://prefeitura.sp.gov.br/web/saude/w/vigilancia_em_saude/boletim_covisa/267596

https://g1.globo.com/sp/sao-paulo/noticia/2025/05/22/mortes-por-dengue-na-cidade-de-sp-sobem-para-14-itaim-bibi-e-outros-44-bairros-tem-epidemia-da-doenca.ghtml

https://portalsinan.saude.gov.br/calendario-epidemiologico
