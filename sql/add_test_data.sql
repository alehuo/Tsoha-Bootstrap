INSERT INTO Vastuuyksikko (nimi) VALUES ('Tietojenkäsittelytieteen laitos');
INSERT INTO Vastuuyksikko (nimi) VALUES ('Matematiikan ja tilastotieteen laitos');
INSERT INTO Kurssi (kurssinimi, kuvaus, opintopisteet, arvosteluTyyppi, aloitusPvm, lopetusPvm, vastuuYksikkoId) VALUES('Ohjelmoinnin perusteet','Kurssilla tutustutaan Java-ohjelmointiin.','5','0','1491004800','1496275200','1');
INSERT INTO Kurssi (kurssinimi, kuvaus, opintopisteet, arvosteluTyyppi, aloitusPvm, lopetusPvm, vastuuYksikkoId) VALUES('Ohjelmoinnin jatkokurssi','Kurssilla syvennytään ohjelmoinnin perusteiden asioihin.','5','0','1491004800','1496275200','1');
INSERT INTO Kurssi (kurssinimi, kuvaus, opintopisteet, arvosteluTyyppi, aloitusPvm, lopetusPvm, vastuuYksikkoId) VALUES('Johdatus yliopistomatematiikkaan','Sisältää mm. joukko-opin ja kompleksiluvut.','5','0','1491004800','1496275200','2');
INSERT INTO Kurssi (kurssinimi, kuvaus, opintopisteet, arvosteluTyyppi, aloitusPvm, lopetusPvm, vastuuYksikkoId) VALUES('Lineaarialgebra ja matriisilaskenta I','Matriisien laskutoimitukset ja sovellukset','5','0','1491004800','1496275200','2');
INSERT INTO Opetusaika (huone,viikonpaiva,aloitusAika,lopetusAika,kurssiId,tyyppi) VALUES('A111','0','795','840','1','0');
INSERT INTO Opetusaika (huone,viikonpaiva,aloitusAika,lopetusAika,kurssiId,tyyppi) VALUES('A111','2','795','840','2','0');
INSERT INTO Opetusaika (huone,viikonpaiva,aloitusAika,lopetusAika,kurssiId,tyyppi) VALUES('A111','3','795','840','3','0');
INSERT INTO Kayttaja (tyyppi,nimi,salasana) VALUES ('1','admin','$2a$07$u83cCmViLjABIinOhXwWaOt6yyzxCSInw3qo5Q7lBXS3AkFeHuj3O'); --admin::admin
INSERT INTO KurssiIlmoittautuminen (kurssiId,kayttajaId) VALUES('1','1');
INSERT INTO KurssiIlmoittautuminen (kurssiId,kayttajaId) VALUES('2','1');
INSERT INTO Kurssisuoritus (kurssiId, kayttajaId, arvosana, paivays) VALUES('3','1','5','1541203200');
