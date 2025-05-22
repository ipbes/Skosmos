
# Knowledge gaps: Specific
PREFIX ipbes: <http://ontology.ipbes.net/report>
SELECT ?gap ?subchapter ?chapter ?description
WHERE {
  ?gap a ipbes:KnowledgeGap .
  OPTIONAL { ?gap ipbes:SubChapter ?subchapter }
  OPTIONAL { ?gap ipbes:hasDescription ?description }
}
ORDER BY ?gap

# Knowledge gaps: Query all graphs: Make sure Select Target Graph is empty (i.e. no graph should be selected)
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
