## SIN5033-Projeto
Projeto - Sistema de recomendação baseado em colaboração e em conteúdo


## Consulta instâncias da classe usuário

```sparql
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX : <http://www.exemplo.org/arboviroses#>

SELECT DISTINCT ?recurso ?nota WHERE {
  ?recurso rdf:type :RecursoEducacional .
  OPTIONAL { ?recurso :temNota ?nota . }
}
ORDER BY ?recurso
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
