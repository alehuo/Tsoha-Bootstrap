INSERT INTO Vastuuyksikko (nimi) VALUES ('Tietojenkäsittelytieteen laitos');
INSERT INTO Vastuuyksikko (nimi) VALUES ('Matematiikan ja tilastotieteen laitos');
INSERT INTO Kurssi (kurssinimi, kuvaus, opintopisteet, aloitusPvm, lopetusPvm, vastuuYksikkoId) VALUES('Ohjelmoinnin perusteet','Kurssilla tutustutaan Java-ohjelmointiin.','5','1476057600','1486684800','1');
INSERT INTO Kurssi (kurssinimi, kuvaus, opintopisteet, aloitusPvm, lopetusPvm, vastuuYksikkoId) VALUES('Ohjelmoinnin jatkokurssi','Kurssilla syvennytään ohjelmoinnin perusteiden asioihin.','5','1476057600','1486684800','1');
INSERT INTO Kurssi (kurssinimi, kuvaus, opintopisteet, aloitusPvm, lopetusPvm, vastuuYksikkoId) VALUES('Johdatus yliopistomatematiikkaan','Sisältää mm. joukko-opin ja kompleksiluvut.','5','1476057600','1486684800','2');
INSERT INTO Kurssi (kurssinimi, kuvaus, opintopisteet, aloitusPvm, lopetusPvm, vastuuYksikkoId) VALUES('Lineaarialgebra ja matriisilaskenta I','Matriisien laskutoimitukset ja sovellukset','5','1476057600','1486684800','2');
INSERT INTO Opetusaika (viikonpaiva,aloitusAika,lopetusAika,kurssiId,tyyppi) VALUES('0','795','840','1','0');
INSERT INTO Kayttaja (tyyppi,nimi,salasana) VALUES ('1','Pekka Pääkäyttäjä','$2a$07$u83cCmViLjABIinOhXwWaOt6yyzxCSInw3qo5Q7lBXS3AkFeHuj3O');
INSERT INTO KurssiIlmoittautuminen (kurssiId,kayttajaId) VALUES('1','1');
INSERT INTO KurssiIlmoittautuminen (kurssiId,kayttajaId) VALUES('2','1');
INSERT INTO Kurssisuoritus (kurssiId, kayttajaId, arvosana, paivays) VALUES('3','1','5','1541203200');
