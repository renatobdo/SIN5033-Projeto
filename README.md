## SIN5033-Projeto

Projeto - Sistema de recomendação baseado em colaboração e em conteúdo

## Recursos
```
[Frontend Laravel]
       |
       v
[Backend Laravel] ---------+
       |                   |
       |                [FastAPI Ontology Microservice]
       |                       |
       |                       v
[Usuário responde quiz]   [Consulta ontologia via OWLReady2]
       |                       |
[Recebe recomendação]   [Retorna recursos educacionais]
```

## Requisitos

![image](https://github.com/user-attachments/assets/5cd3fbc6-749f-4401-96c9-81d05dc2202d)

### subir o servidor
cd C:\testes\Ontologias
uvicorn main:app --reload

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

SELECT DISTINCT ?usuario ?nome ?email ?idade WHERE {
  ?usuario rdf:type :Usuario .
  OPTIONAL { ?usuario :temNome ?nome . }
  OPTIONAL { ?usuario :temEmail ?email . }
  OPTIONAL { ?usuario :temIdade ?idade . }
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
