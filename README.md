## SIN5033-Projeto

Projeto - Sistema de recomendação baseado em colaboração e em conteúdo

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
SELECT ?recurso ?tipo ?nota WHERE {
  :usuario<nome> :temPreferenciaTipo ?tipo .
  ?recurso a :RecursoEducacional ;
           :temTipo ?tipo ;
           :temNota ?nota .
}
ORDER BY DESC(?nota)
LIMIT 5
```
O que faz:

    Pega os tipos de preferência (:temPreferenciaTipo) associados ao usuário.

    Busca recursos (:RecursoEducacional) que tenham esse mesmo tipo (:temTipo).

    Ordena os resultados pela nota (:temNota), do maior para o menor.

    Limita a 5 recursos.

    Exemplo: Se o usuário gosta de "vídeo", o sistema traz os vídeos mais bem avaliados.

✅ 2. Recomendação por Colaboração

Objetivo: recomendar recursos que foram acessados por outros usuários com as mesmas preferências.
SPARQL usado:
```
SELECT ?recurso ?tipo ?nota WHERE {
  :usuario<nome> :temPreferenciaTipo ?tipo .
  ?outroUsuario :temPreferenciaTipo ?tipo .
  FILTER(?outroUsuario != :usuario<nome>)
  ?recurso a :RecursoEducacional ;
           :temTipo ?tipo ;
           :temNota ?nota .
}
ORDER BY DESC(?nota)
LIMIT 5
```
O que faz:

    Identifica outros usuários que compartilham o mesmo tipo de preferência do usuário logado.

    Recupera os recursos que esses outros usuários também acessaram com esse tipo.

    Ordena pela nota e retorna até 5.

    Exemplo: Se outro usuário também gosta de vídeos e deu nota 5 para um infográfico, esse infográfico será sugerido — mesmo que o usuário atual ainda não o tenha visto.

```
PREFIX : <http://www.exemplo.org/arboviroses#>

SELECT DISTINCT ?recurso ?tipo ?nota ?nome ?email ?idade WHERE {
  :renato :temPreferenciaTipo ?tipo_comum .

  ?outro :temPreferenciaTipo ?tipo_comum .
  FILTER (?outro != :renato)

  ?outro :temPreferenciaTipo ?tipo .
  FILTER NOT EXISTS { :renato :temPreferenciaTipo ?tipo }

  ?recurso a :RecursoEducacional ;
           :temTipo ?tipo ;
           :temNota ?nota .

  OPTIONAL { ?outro :temNome ?nome }
  OPTIONAL { ?outro :temEmail ?email }
  OPTIONAL { ?outro :temIdade ?idade }
}
ORDER BY DESC(?nota)
LIMIT 5
```

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

## Comandos para exportar os dados (cmd do windows) do apache jena fuseki para arquivo dados.ttl

curl -H "Accept: text/turtle" --data-urlencode "query=CONSTRUCT { ?s ?p ?o } WHERE { ?s ?p ?o }" http://localhost:3030/arboviroses/query -o dados.ttl

## Para dar flush no database do apache jena

Entre na pasta  C:\apache-jena-fuseki-5.4.0\run\databases e remova os databases

## Referências

https://portalsinan.saude.gov.br/dados-epidemiologicos-sinan

http://tabnet.datasus.gov.br/cgi/deftohtm.exe?sinannet/cnv/denguebsp.def

https://prefeitura.sp.gov.br/documents/d/saude/documento-tecnico_-dengue_2025-pdf

https://docs.google.com/spreadsheets/d/1a46T0khfDvURwdXQ6CAovSgufJffJBhcW6mmBxSomUI/edit?usp=sharing 

https://nies.saude.sp.gov.br/ses/publico/dengue 

