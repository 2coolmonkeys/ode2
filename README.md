ODE Semantic Toolkit
====


1: We downloaden p2000 meldingen van [2] dit is een geo rss feed.

2:  per x,y, zoeken we het bag pand.

3:  bag info van de live omgeving kopieren we naar de test omgeving zie [3]

4: De p2000 melding stoppen we in de laag [4]  


5: Vervolgens vullen we 5 stub lagen met random data, die ons moet gaan helpen in de use case:

-*GBA, aantal inwoners [5]
* Vergunningen, [6]
* WMO: [7]
* Woningwaarde [8], en 
* aanrijtijd, [9]

Dit is uiteraard een experimentele omgeving, waar we een gevoel voor het soort data krijgen.

---

[1] http://citysdk.waag.org/map#http://test-api.citysdk.waag.org/nodes?layer=2cm.*&per_page=100

[2] http://feeds.livep2000.nl

[3] http://citysdk.waag.org/map#http://test-api.citysdk.waag.org/nodes?layer=2cm.bag.vbo&per_page=10 

[4] http://citysdk.waag.org/map#http://test-api.citysdk.waag.org/nodes?layer=2cm.p2000bag&per_page=10

[5] http://citysdk.waag.org/map#http://test-api.citysdk.waag.org/nodes?layer=2cm.stub_gba&per_page=10

[6] http://citysdk.waag.org/map#http://test-api.citysdk.waag.org/nodes?layer=2cm.stub_vergunningen&per_page=10

[7] http://citysdk.waag.org/map#http://test-api.citysdk.waag.org/nodes?layer=2cm.stub_wmo&per_page=10

[8] http://citysdk.waag.org/map#http://test-api.citysdk.waag.org/nodes?layer=2cm.stub_woningwaarde&per_page=10

[9] http://citysdk.waag.org/map#http://test-api.citysdk.waag.org/nodes?layer=2cm.aanrijtijd&per_page=10
[10] http://p2000.citysdk.nl