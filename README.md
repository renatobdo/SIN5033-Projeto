# SIN5033-Projeto
Projeto - Sistema de recomendação baseado em colaboração e em conteúdo


# Consulta instâncias da classe usuário

PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX : <http://www.exemplo.org/arboviroses#>

SELECT DISTINCT ?usuario ?nome ?email ?idade WHERE {
  ?usuario rdf:type :Usuario .
  OPTIONAL { ?usuario :temNome ?nome . }
  OPTIONAL { ?usuario :temEmail ?email . }
  OPTIONAL { ?usuario :temIdade ?idade . }
}
ORDER BY ?usuario


# Consulta recursos educacionais

PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX : <http://www.exemplo.org/arboviroses#>

SELECT DISTINCT ?recurso ?nota WHERE {
  ?recurso rdf:type :RecursoEducacional .
  OPTIONAL { ?recurso :temNota ?nota . }
}
ORDER BY ?recurso

