foaf:prefLabel
skos:prefLabel

ipbes:Report\s*<http://ontology\.ipbes\.net/report/([^>]+)>
ipbes:hasReport ipbes-report:$1

"http://ontology\.ipbes\.net/report/([^>]+)"
ipbes-report:$1

<http://ontology\.ipbes\.net/report/([^>]+)>
ipbes-report:$1

<http://ontology\.ipbes\.net/report/ch/([^>]+)>
ipbes-ch:$1

<http://ontology\.ipbes\.net/report/sch/([^>]+)>
ipbes-sch:$1

<http://ontology\.ipbes\.net/report/key/([^>]+)>
ipbes-key:$1

<http://ontology\.ipbes\.net/report/bgm/([^>]+)>
ipbes-bgm:$1

<http://ontology\.ipbes\.net/report/subm/([^>]+)>
ipbes-subm:$1

<http://ontology\.ipbes\.net/report/kg/([^>]+)>
ipbes-kg:$1

<http://ontology\.ipbes\.net/report/il/([^>]+)>
ipbes-il:$1

<http://ontology\.ipbes\.net/report/person/([^>]+)>
ipbes-person:$1

<http://ontology\.ipbes\.net/report/ref/([^/]+)/items/([^>]+)>
<http://data.ipbes.net/refm/$1/items/$2>

(ipbes:identifier\s*")([^"]+)(")
$1$2"@en

(skos:prefLabel\s*")([^"]+)(")
$1$2"@en

(foaf:firstName\s*")([^"]+)(")
$1$2"@en

(foaf:lastName\s*")([^"]+)(")
$1$2"@en

## remove spaces and fullstop
<http://ontology.ipbes.net/report/key/global19- C.>
<http://ontology.ipbes.net/report/key/global19-C>

## wrap urls where authors have apostrophes
ipbes-person:o’callaghan_cristina a foaf:Person ;
<ipbes-person:o’callaghan_cristina> a foaf:Person ;

## remove brackets e.g. 
ipbes-sch:global19-3.3.2.2(SustainableDevelopmentGoal3) ;
ipbes-sch:global19-2.3.5.2(NCP2and12) ;
Expected punctuation to follow "http://data.ipbes.net/sch/global19-3.3.2.2" at line 44985.
Expected punctuation to follow "http://data.ipbes.net/sch/global19-2.3.5.2" at line 45000.

# remove trailing fullstops
(ipbes-sch:[^\s;]+)(?<=\d)\.([\s;])
$1$2
(ipbes-sch:[^\s;]+)(?<=\d)\.(?=[\s;,])
$1
(ipbes-person:[^\s;]+)(?<=\w)\.(?=[\s;])
$1

example
ipbes-sch:global19-2.1.2. ;
ipbes:SubChapter ipbes-sch:global19-4.2.4.3.,
Expected subject but got ; at line 45502.


***********************************************************************
Run tests 
***********************************************************************
https://www.w3.org/2015/03/ShExValidata/
https://felixlohmeier.github.io/turtle-web-editor/

