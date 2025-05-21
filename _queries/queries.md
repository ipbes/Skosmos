# WHEN DATA IS IN DEFAULT GRAPH: Get all knowledge gaps and get additional information through joins. here we connect Chapter with nested optional

prefix ipbes: <http://ontology.ipbes.net/report>  
SELECT ?gap ?subchapter ?chapter  ?description 
WHERE { 
    ?gap a ipbes:KnowledgeGap . 
    OPTIONAL { ?gap ipbes:SubChapter ?subchapter } 
    OPTIONAL { ?gap ipbes:hasDescription ?description } 
} 
ORDER BY ?gap

# WHEN DATA IS IN SPECIFIC GRAPH (1) QUERY ALL GRAPHS:  Get all knowledge gaps in a specific assessment identified using a specific graph name and get additional information through joins. Here we connect Chapter with nested optional
PREFIX ipbes: <http://ontology.ipbes.net/report>
SELECT ?g ?gap ?subchapter ?chapter ?description
WHERE {
  GRAPH ?g {
    ?gap a ipbes:KnowledgeGap .
    OPTIONAL { ?gap ipbes:SubChapter ?subchapter }
    OPTIONAL { ?gap ipbes:hasDescription ?description }
  }
}
ORDER BY ?gap

# WHEN DATA IS IN SPECIFIC GRAPH (2) QUERY SPECIFIC GRAPH:  Get all knowledge gaps in a specific assessment identified using a specific graph name and get additional information through joins. Here we connect Chapter with nested optional

prefix ipbes: <http://ontology.ipbes.net/report>  

SELECT ?gap ?subchapter ?chapter  ?description 
  
WHERE { 
    GRAPH <http://ontology.ipbes.net/graph/ias> { 
    ?gap a ipbes:KnowledgeGap . 
    OPTIONAL { ?gap ipbes:SubChapter ?subchapter } 
    OPTIONAL { ?gap ipbes:hasDescription ?description } 
    }
} 
ORDER BY ?gap

### Name of all graphs: 
SELECT ?g 
WHERE { 
GRAPH ?g { } 
} 
  
### Deleting one specific graph: 
  
DROP GRAPH <http://ontology.ipbes.net/graph/ias> 
  

### Total number of triples in a graph: 
  
SELECT (COUNT(*) as ?count) 
where{ 
Graph <http://ontology.ipbes.net/graph/ias> { 

?x ?z ?y } 
} 
  
### Querying from a specific graph: 
  
WHERE{ 
GRAPH <http://ontology.ipbes.net/graph/ias> { 

?x ?y ?z 
} 

---

SELECT ?subject ?predicate ?object
WHERE {
  GRAPH <http://ontology.ipbes.net/graph/ias> { 
  ?subject ?predicate ?object
  }
}
LIMIT 25
