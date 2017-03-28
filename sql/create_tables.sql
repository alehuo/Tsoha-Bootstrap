CREATE TABLE Vastuuyksikko(
    id SERIAL PRIMARY KEY,
    nimi varchar(100) NOT NULL
);
CREATE TABLE Kurssi(
    id SERIAL PRIMARY KEY,
    kurssinimi varchar(50) NOT NULL,
    kuvaus varchar(255) NOT NULL,
    opintoPisteet INTEGER DEFAULT 5,
    aloitusPvm INTEGER,
    lopetusPvm INTEGER,
    vastuuYksikkoId INTEGER REFERENCES Vastuuyksikko(id)
);
CREATE TABLE Kayttaja(
    id SERIAL PRIMARY KEY,
    tyyppi INTEGER DEFAULT 0,
    nimi varchar(100) NOT NULL UNIQUE,
    salasana varchar(255) NOT NULL
);
CREATE TABLE KurssiIlmoittautuminen(
    kurssiId INTEGER REFERENCES Kurssi(id),
    kayttajaId INTEGER REFERENCES Kayttaja(id)
);
CREATE TABLE Kurssisuoritus(
    id SERIAL PRIMARY KEY,
    kurssiId INTEGER REFERENCES Kurssi(id),
    kayttajaId INTEGER REFERENCES Kayttaja(id),
    arvosana INTEGER DEFAULT 0,
    paivays INTEGER
);
CREATE TABLE Opetusaika(
    id SERIAL PRIMARY KEY,
    viikonpaiva INTEGER,
    aloitusAika INTEGER,
    lopetusAika INTEGER,
    kurssiId INTEGER REFERENCES Kurssi(id),
    tyyppi INTEGER DEFAULT 0
);
