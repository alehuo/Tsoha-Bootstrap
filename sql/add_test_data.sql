--Laitokset
INSERT INTO Laitos (nimi) VALUES ('Tietojenkäsittelytieteen laitos');
INSERT INTO Laitos (nimi) VALUES ('Matematiikan ja tilastotieteen laitos');
--Kurssit
INSERT INTO Kurssi (nimi, aloitusPvm, lopetusPvm, laitosId) VALUES('Ohjelmoinnin perusteet','10.10.2016','10.1.2017',1);
INSERT INTO Kurssi (nimi, aloitusPvm, lopetusPvm, laitosId) VALUES('Ohjelmoinnin jatkokurssi','11.1.2017','11.4.2017',1);
INSERT INTO Kurssi (nimi, aloitusPvm, lopetusPvm, laitosId) VALUES('Johdatus yliopistomatematiikkaan','2.1.2017','2.4.2017',2);
INSERT INTO Kurssi (nimi, aloitusPvm, lopetusPvm, laitosId) VALUES('Lineaarialgebra ja matriisilaskenta I','2.1.2017','2.4.2017',2);