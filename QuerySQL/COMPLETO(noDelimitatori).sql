DROP DATABASE IF EXISTS PIATTAFORMA_ESQL;
CREATE DATABASE PIATTAFORMA_ESQL;
DROP DATABASE IF EXISTS PROGETTO_BASI;
CREATE DATABASE PROGETTO_BASI;
USE PROGETTO_BASI;


CREATE TABLE IF NOT EXISTS Docente (
  email_docente VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  nome VARCHAR(50) NOT NULL,
  cognome VARCHAR(50) NOT NULL,
  nome_dipartimento VARCHAR(100) NOT NULL,
  nome_corso VARCHAR(100) NOT NULL,
  PRIMARY KEY (email_docente)
);

CREATE TABLE IF NOT EXISTS Studente (
  email_studente VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  nome VARCHAR(50) NOT NULL,
  cognome VARCHAR(50) NOT NULL,
  anno_immatricolazione INT NOT NULL,
  codice_alfanumerico VARCHAR(16) UNIQUE NOT NULL,
  PRIMARY KEY (email_studente)
);

CREATE TABLE IF NOT EXISTS Telefono_Docente (
  email_docente VARCHAR(255) NOT NULL,
  rec_telefonico VARCHAR(255) NOT NULL,
  PRIMARY KEY (email_docente, rec_telefonico),
  FOREIGN KEY (email_docente) REFERENCES Docente(email_docente)
);

CREATE TABLE IF NOT EXISTS Telefono_Studente (
  email_studente VARCHAR(255) NOT NULL,
  rec_telefonico VARCHAR(255) NOT NULL,
  PRIMARY KEY (email_studente, rec_telefonico),
  FOREIGN KEY (email_studente) REFERENCES Studente(email_studente)
);

CREATE TABLE IF NOT EXISTS Test (
  titolo VARCHAR(255) NOT NULL,
  data_creazione DATE NOT NULL,
  visualizza_risposte BOOLEAN DEFAULT false,
  email_docente VARCHAR(255) NOT NULL,
  PRIMARY KEY (titolo, email_docente),
  FOREIGN KEY (email_docente) REFERENCES Docente(email_docente)
);

CREATE TABLE IF NOT EXISTS Foto_Test (
  titolo_test VARCHAR(255) NOT NULL,
  email_docente VARCHAR(255) NOT NULL,
  foto BLOB NOT NULL,
  PRIMARY KEY (titolo_test, email_docente),
  FOREIGN KEY (email_docente) REFERENCES Docente(email_docente),
  FOREIGN KEY (titolo_test) REFERENCES Test(titolo)
);

CREATE TABLE IF NOT EXISTS Messaggio_Studente (
  id INT AUTO_INCREMENT,
  data_messaggio DATE NOT NULL,
  titolo VARCHAR(255) NOT NULL,
  testo TEXT NOT NULL,
  email_studente VARCHAR(255) NOT NULL,
  email_docente VARCHAR(255) NOT NULL,
  titolo_test VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (email_studente) REFERENCES Studente(email_studente),
  FOREIGN KEY (email_docente) REFERENCES Docente(email_docente),
  FOREIGN KEY (titolo_test) REFERENCES Test(titolo)
);

CREATE TABLE IF NOT EXISTS Messaggio_Docente (
  id INT AUTO_INCREMENT,
  data_messaggio DATE NOT NULL,
  titolo VARCHAR(255) NOT NULL,
  testo TEXT NOT NULL,
  email_docente VARCHAR(255) NOT NULL,
  titolo_test VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (email_docente) REFERENCES Docente(email_docente),
  FOREIGN KEY (titolo_test) REFERENCES Test(titolo)
);


CREATE TABLE IF NOT EXISTS TabellaSQL (
  nome_tabella VARCHAR(255) NOT NULL,
  data_creazione DATE NOT NULL,
  num_righe INT NOT NULL,
  email_docente VARCHAR(255) NOT NULL,
  PRIMARY KEY (nome_tabella, email_docente),
  FOREIGN KEY (email_docente) REFERENCES Docente(email_docente)
);

CREATE TABLE IF NOT EXISTS Attributo (
  id INT AUTO_INCREMENT,
  nome_tabella VARCHAR(255) NOT NULL,
  nome_attributo VARCHAR(255) NOT NULL,
  tipo VARCHAR(255) NOT NULL,
  chiave BOOLEAN DEFAULT false,
  PRIMARY KEY (id),
  FOREIGN KEY (nome_tabella) REFERENCES TabellaSQL(nome_tabella)
);

CREATE TABLE IF NOT EXISTS Vincolo_Di_Integrita (
  id_attributo_referenziante INT NOT NULL,
  id_attributo_referenziato INT NOT NULL,
  PRIMARY KEY (id_attributo_referenziante, id_attributo_referenziato),
  FOREIGN KEY (id_attributo_referenziante) REFERENCES Attributo(id),
  FOREIGN KEY (id_attributo_referenziato) REFERENCES Attributo(id)
);

CREATE TABLE IF NOT EXISTS Studente_Svolge_Test (
  data_ultima_risposta DATE NOT NULL,
  data_prima_risposta DATE NOT NULL,
  email_studente VARCHAR(255) NOT NULL,
  titolo_test VARCHAR(255) NOT NULL,
  stato ENUM('Aperto', 'InCompletamento', 'Concluso') NOT NULL,
  PRIMARY KEY (email_studente, titolo_test),
  FOREIGN KEY (email_studente) REFERENCES Studente(email_studente),
  FOREIGN KEY (titolo_test) REFERENCES Test(titolo)
);

CREATE TABLE IF NOT EXISTS Quesito (
  numero_progressivo INT NOT NULL,
  titolo_test VARCHAR(255) NOT NULL,
  difficolta ENUM('Basso', 'Medio', 'Alto') NOT NULL,
  descrizione VARCHAR(255) NOT NULL,
  tipo ENUM('Chiuso', 'Codice') NOT NULL,
  num_risposte INT NOT NULL,
  PRIMARY KEY (numero_progressivo, titolo_test),
  FOREIGN KEY (titolo_test) REFERENCES Test(titolo)
);

CREATE TABLE IF NOT EXISTS Quesito_Riferisce_Tabella (
  numero_quesito INT NOT NULL,
  titolo_test VARCHAR(255) NOT NULL,
  nome_tabella_sql VARCHAR(255) NOT NULL,
  PRIMARY KEY (numero_quesito, titolo_test, nome_tabella_sql),
  FOREIGN KEY (numero_quesito, titolo_test) REFERENCES Quesito(numero_progressivo, titolo_test),
  FOREIGN KEY (nome_tabella_sql) REFERENCES TabellaSQL(nome_tabella)
);

CREATE TABLE IF NOT EXISTS Opzione (
  numerazione INT NOT NULL,
  numero_quesito INT NOT NULL,
  titolo_test VARCHAR(255) NOT NULL,
  campo_testo VARCHAR(255) NOT NULL,
  corretta BOOLEAN NOT NULL,
  PRIMARY KEY (numerazione, numero_quesito, titolo_test),
  FOREIGN KEY (numero_quesito, titolo_test) REFERENCES Quesito(numero_progressivo, titolo_test)
);

CREATE TABLE IF NOT EXISTS RispostaChiusa (
  id INT AUTO_INCREMENT,
  esito BOOLEAN NOT NULL,
  email_studente VARCHAR(255),
  numero_quesito INT NOT NULL,
  numero_opzione INT NOT NULL,
  titolo_test VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (email_studente) REFERENCES Studente(email_studente),
  FOREIGN KEY (numero_quesito, titolo_test) REFERENCES Quesito(numero_progressivo, titolo_test),
  FOREIGN KEY (numero_opzione, numero_quesito, titolo_test) REFERENCES Opzione(numerazione, numero_quesito, titolo_test)
);

CREATE TABLE IF NOT EXISTS RispostaCodice (
  id INT AUTO_INCREMENT,
  esito BOOLEAN NOT NULL,
  email_studente VARCHAR(255),
  testoSQL TEXT NOT NULL,
  numero_quesito INT NOT NULL,
  titolo_test VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (email_studente) REFERENCES Studente(email_studente),
  FOREIGN KEY (numero_quesito, titolo_test) REFERENCES Quesito(numero_progressivo, titolo_test)
);

CREATE TABLE IF NOT EXISTS Soluzione_Codice (
  numerazione INT NOT NULL,
  numero_quesito INT NOT NULL,
  titolo_test VARCHAR(255) NOT NULL,
  codice TEXT NOT NULL,
  PRIMARY KEY (numerazione, numero_quesito, titolo_test),
  FOREIGN KEY (numero_quesito, titolo_test) REFERENCES Quesito(numero_progressivo, titolo_test)
);


CREATE TABLE IF NOT EXISTS TestCompleti(
  email_studente VARCHAR(255) NOT NULL,
  titolo_test VARCHAR(255) NOT NULL,
  punteggio INT NOT NULL,
  totale INT NOT NULL,
  PRIMARY KEY (email_studente, titolo_test),
  FOREIGN KEY (email_studente) REFERENCES Studente(email_studente),
  FOREIGN KEY (titolo_test) REFERENCES Test(titolo)
 );


CREATE TABLE IF NOT EXISTS TabellaAppoggio (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome_tabella VARCHAR(255) NOT NULL,
  FOREIGN KEY(nome_tabella) REFERENCES TabellaSQL(nome_tabella)
);




DROP VIEW IF EXISTS Classifica_Studenti_Test_Completati;
CREATE VIEW Classifica_Studenti_Test_Completati AS
SELECT S.codice_alfanumerico, COUNT(*) AS NumeroTestCompletati
FROM Studente_Svolge_Test SST
JOIN Studente S ON SST.email_studente = S.email_studente
WHERE SST.stato = 'Concluso'
GROUP BY S.codice_alfanumerico
ORDER BY NumeroTestCompletati DESC;


DROP VIEW IF EXISTS Classifica_Studenti_Accuratezza;
CREATE VIEW Classifica_Studenti_Accuratezza AS
SELECT 
    S.codice_alfanumerico,
    SUM(CASE WHEN Risposte.esito = TRUE THEN 1 ELSE 0 END) AS RisposteCorrette,
    COUNT(*) AS TotaleRisposte,
    (SUM(CASE WHEN Risposte.esito = TRUE THEN 1 ELSE 0 END) / COUNT(*)) * 100 AS PercentualeCorrettezza
FROM (
    SELECT email_studente, esito FROM RispostaChiusa
    UNION ALL
    SELECT email_studente, esito FROM RispostaCodice
) AS Risposte
JOIN Studente S ON Risposte.email_studente = S.email_studente
GROUP BY 
    S.codice_alfanumerico
ORDER BY 
    PercentualeCorrettezza DESC;
    
    
DROP VIEW IF EXISTS Classifica_Quesiti_Risposte;
CREATE VIEW Classifica_Quesiti_Risposte AS
SELECT 
    Q.numero_progressivo,
    Q.titolo_test,
    Q.descrizione,
    Q.difficolta,
    Q.tipo,
    (IFNULL(RC.NumRisposteChiuse, 0) + IFNULL(RS.NumRisposteCodice, 0)) AS TotaleRisposteInserite
FROM 
    Quesito Q
LEFT JOIN 
    (SELECT 
        numero_quesito, 
        titolo_test, 
        COUNT(*) AS NumRisposteChiuse 
     FROM 
        RispostaChiusa 
     GROUP BY 
        numero_quesito, titolo_test
    ) RC ON Q.numero_progressivo = RC.numero_quesito AND Q.titolo_test = RC.titolo_test
LEFT JOIN 
    (SELECT 
        numero_quesito, 
        titolo_test, 
        COUNT(*) AS NumRisposteCodice 
     FROM 
        RispostaCodice 
     GROUP BY 
        numero_quesito, titolo_test
    ) RS ON Q.numero_progressivo = RS.numero_quesito AND Q.titolo_test = RS.titolo_test
ORDER BY 
    TotaleRisposteInserite DESC;






/* ******************************************************************   STORED PROCEDURE ************************************************** */ 
DROP PROCEDURE IF EXISTS REGISTRAZIONE_DOCENTE;
CREATE PROCEDURE REGISTRAZIONE_DOCENTE(
  IN p_email_docente VARCHAR(255),
  IN p_password VARCHAR(255),
  IN p_nome VARCHAR(255),
  IN p_cognome VARCHAR(255),
  IN p_nome_dipartimento VARCHAR(255),
  IN p_nome_corso VARCHAR(255),
  IN p_rec_telefonico VARCHAR(255),
  OUT p_success BOOLEAN
)
BEGIN
  DECLARE email_count_docente INT;
  DECLARE email_count_studente INT;

  -- Controlla se l'email esiste già nella tabella Docente
  SELECT COUNT(*) INTO email_count_docente FROM Docente WHERE email_docente = p_email_docente;
  
  -- Controlla se l'email esiste già nella tabella Studente
  SELECT COUNT(*) INTO email_count_studente FROM Studente WHERE email_studente = p_email_docente;

  -- Se l'email è già utilizzata da un Docente o da uno Studente, fallisce la registrazione
  IF email_count_docente > 0 OR email_count_studente > 0 THEN
    SET p_success = FALSE;
  ELSE
    -- Crittografa la password
    SET p_password = SHA2(p_password, 256);

    -- Inserisci il nuovo docente
    INSERT INTO Docente(email_docente, password, nome, cognome, nome_dipartimento, nome_corso)
    VALUES (p_email_docente, p_password, p_nome, p_cognome, p_nome_dipartimento, p_nome_corso);

    -- Inserisci il numero di telefono se fornito
    IF p_rec_telefonico IS NOT NULL THEN
      INSERT INTO Telefono_Docente(email_docente, rec_telefonico)
      VALUES (p_email_docente, p_rec_telefonico);
    END IF;

    SET p_success = TRUE;
  END IF;
END;


DROP PROCEDURE IF EXISTS REGISTRAZIONE_STUDENTE;
CREATE PROCEDURE REGISTRAZIONE_STUDENTE(
  IN p_email_studente VARCHAR(255),
  IN p_password VARCHAR(255),
  IN p_nome VARCHAR(255),
  IN p_cognome VARCHAR(255),
  IN p_anno_immatricolazione INT,
  IN p_codice_alfanumerico VARCHAR(16),
  IN p_rec_telefonico VARCHAR(255),
  OUT p_success BOOLEAN
)
BEGIN
  DECLARE email_count_studente INT;
  DECLARE email_count_docente INT;
  DECLARE code_count INT;

  -- Controlla se l'email esiste già nella tabella Studente
  SELECT COUNT(*) INTO email_count_studente FROM Studente WHERE email_studente = p_email_studente;
  
  -- Controlla se l'email esiste già nella tabella Docente
  SELECT COUNT(*) INTO email_count_docente FROM Docente WHERE email_docente = p_email_studente;

  -- Controlla se il codice alfanumerico esiste già
  SELECT COUNT(*) INTO code_count FROM Studente WHERE codice_alfanumerico = p_codice_alfanumerico;

  -- Se l'email o il codice alfanumerico sono già utilizzati, fallisce la registrazione
  IF (email_count_studente > 0 OR email_count_docente > 0 OR code_count > 0) THEN
    SET p_success = FALSE;
  ELSE
    -- Crittografa la password
    SET p_password = SHA2(p_password, 256);

    -- Inserisci il nuovo studente
    INSERT INTO Studente(email_studente, password, nome, cognome, anno_immatricolazione, codice_alfanumerico)
    VALUES (p_email_studente, p_password, p_nome, p_cognome, p_anno_immatricolazione, p_codice_alfanumerico);

    -- Inserisci il numero di telefono se fornito
    IF p_rec_telefonico IS NOT NULL THEN
      INSERT INTO Telefono_Studente(email_studente, rec_telefonico)
      VALUES (p_email_studente, p_rec_telefonico);
    END IF;

    SET p_success = TRUE;
  END IF;
END;

DROP PROCEDURE IF EXISTS AUTENTICAZIONE_UTENTE;
CREATE PROCEDURE AUTENTICAZIONE_UTENTE(
  IN p_email VARCHAR(255),
  IN p_password VARCHAR(255),
  OUT p_status INT,
  OUT p_user_type VARCHAR(10),
  OUT p_nome VARCHAR(255)
)
BEGIN
  SET p_status = 0;
  SET p_user_type = 'nessuno';

  IF EXISTS(SELECT * FROM Studente WHERE email_studente = p_email) THEN
    SET p_status = 1;
    IF EXISTS(SELECT * FROM Studente WHERE email_studente = p_email AND password = SHA2(p_password, 256)) THEN
      SET p_status = 2;
      SET p_user_type = 'studente';
      SELECT nome INTO p_nome FROM Studente WHERE email_studente = p_email AND password = SHA2(p_password, 256);
    END IF;
  END IF;

  IF EXISTS(SELECT * FROM Docente WHERE email_docente = p_email) THEN
    SET p_status = 1;
    IF EXISTS(SELECT * FROM Docente WHERE email_docente = p_email AND password = SHA2(p_password, 256)) THEN
      SET p_status = 2;
      SET p_user_type = 'docente';
      SELECT nome INTO p_nome FROM Docente WHERE email_docente = p_email AND password = SHA2(p_password, 256);
    END IF;
  END IF;
END;


DROP PROCEDURE IF EXISTS CREAZIONE_TABELLA;
CREATE PROCEDURE CREAZIONE_TABELLA(
	IN p_nome_tabella VARCHAR(225),
  IN p_email_docente VARCHAR(255)
) 
BEGIN
	INSERT INTO TabellaSQL(nome_tabella, data_creazione, num_righe, email_docente)
  VALUES (p_nome_tabella, CURRENT_DATE(), '0', p_email_docente);
END;


DROP PROCEDURE IF EXISTS NUOVO_ATTRIBUTO;
CREATE PROCEDURE NUOVO_ATTRIBUTO(
	IN p_nome_tabella VARCHAR(255),
  IN p_nome_attributo VARCHAR(255),
  IN p_tipo VARCHAR(255),
  IN p_chiave BOOLEAN
) 
BEGIN
	INSERT INTO Attributo(nome_tabella, nome_attributo, tipo, chiave)
  VALUES (p_nome_tabella, p_nome_attributo, p_tipo, p_chiave);
END;

DROP PROCEDURE IF EXISTS NUOVO_VINCOLO;
CREATE PROCEDURE NUOVO_VINCOLO(
  IN p_nome_attributo1 VARCHAR(255),
  IN p_nome_attributo2 VARCHAR(255),
  IN p_nome_tabella1 VARCHAR(255),
  IN p_nome_tabella2 VARCHAR(255)
)
BEGIN
  DECLARE id_1 INT;
  DECLARE id_2 INT;

  SELECT id INTO id_1
  FROM Attributo
  WHERE nome_attributo = p_nome_attributo1 AND nome_tabella = p_nome_tabella1;

  SELECT id INTO id_2
  FROM Attributo
  WHERE nome_attributo = p_nome_attributo2 AND nome_tabella = p_nome_tabella2;

  INSERT INTO Vincolo_Di_Integrita(id_attributo_referenziante, id_attributo_referenziato)
  VALUES (id_1, id_2);
END;

DROP PROCEDURE IF EXISTS VISUALIZZAZIONE_TABELLA;
CREATE PROCEDURE VISUALIZZAZIONE_TABELLA(
  IN p_email_docente VARCHAR(255)
) 
BEGIN

SELECT nome_tabella, data_creazione, num_righe
FROM TabellaSQL
WHERE email_docente = p_email_docente;

END;


DROP PROCEDURE IF EXISTS NUOVO_TEST;
CREATE PROCEDURE NUOVO_TEST(
	IN p_titolo VARCHAR(225),
  IN p_data_creazione DATE,
  IN p_email_docente VARCHAR(255)
) 
BEGIN
	INSERT INTO Test(titolo, data_creazione, email_docente)
  VALUES (p_titolo, p_data_creazione, p_email_docente);
END;


DROP PROCEDURE IF EXISTS INSERISCI_QUESITO;
CREATE PROCEDURE INSERISCI_QUESITO (
    IN p_titolo_test VARCHAR(255),
    IN p_difficolta ENUM('Basso', 'Medio', 'Alto'),
    IN p_descrizione VARCHAR(255),
    IN p_tipo ENUM('Chiuso', 'Codice'),
    OUT p_numero_progressivo INT
)
BEGIN
    INSERT INTO Quesito (titolo_test, difficolta, descrizione, tipo, num_risposte)
    VALUES (p_titolo_test, p_difficolta, p_descrizione, p_tipo, '0');

    SELECT MAX(numero_progressivo) INTO p_numero_progressivo
    FROM Quesito
    WHERE titolo_test = p_titolo_test;
END;

DROP PROCEDURE IF EXISTS INSERISCI_FOTO_TEST;
CREATE PROCEDURE INSERISCI_FOTO_TEST (
    IN p_titolo_test VARCHAR(255),
    IN p_email_docente VARCHAR(255),
    IN p_foto BLOB
)
BEGIN
    INSERT INTO Foto_test (titolo_test, email_docente, foto)
    VALUES (p_titolo_test, p_email_docente, p_foto);
END;


DROP PROCEDURE IF EXISTS INSERISCI_QUESITO_TABELLA;
CREATE PROCEDURE INSERISCI_QUESITO_TABELLA(
  	IN p_numero_quesito INT,
    IN p_titolo_test VARCHAR(255),
    IN p_nome_tabella_sql VARCHAR(255)
)
BEGIN
    INSERT INTO Quesito_Riferisce_Tabella(numero_quesito, titolo_test, nome_tabella_sql)
    VALUES (p_numero_quesito, p_titolo_test, p_nome_tabella_sql);
END;

DROP PROCEDURE IF EXISTS INSERISCI_OPZIONE;
CREATE PROCEDURE INSERISCI_OPZIONE(
    IN p_numero_quesito INT,
    IN p_titolo_test VARCHAR(255),
    IN p_campo_testo VARCHAR(255),
    IN p_corretta BOOLEAN
)
BEGIN
    INSERT INTO Opzione (numero_quesito, titolo_test, campo_testo, corretta)
    VALUES (p_numero_quesito, p_titolo_test, p_campo_testo, p_corretta);
END;

DROP PROCEDURE IF EXISTS INSERISCI_SOLUZIONE_CODICE;
CREATE PROCEDURE INSERISCI_SOLUZIONE_CODICE(
    IN p_numero_quesito INT,
    IN p_titolo_test VARCHAR(255),
    IN p_codice TEXT
)
BEGIN
    INSERT INTO Soluzione_Codice (numero_quesito, titolo_test, codice)
    VALUES (p_numero_quesito, p_titolo_test, p_codice);
END;

DROP PROCEDURE IF EXISTS VISUALIZZAZIONE_TEST;
CREATE PROCEDURE VISUALIZZAZIONE_TEST(
  IN p_email_docente VARCHAR(255)
)
BEGIN
	SELECT titolo, data_creazione, visualizza_risposte
  FROM Test
 	WHERE email_docente = p_email_docente;
END;


DROP PROCEDURE IF EXISTS VISUALIZZAZIONE_QUESITI;
CREATE PROCEDURE VISUALIZZAZIONE_QUESITI(
  IN p_nome_test VARCHAR(255)
)
BEGIN
    SELECT *
    FROM Quesito
    WHERE p_nome_test = titolo_test;
END;


DROP PROCEDURE IF EXISTS VISUALIZZAZIONE_QUESITO_TABELLA;
CREATE PROCEDURE VISUALIZZAZIONE_QUESITO_TABELLA(
  IN p_numero_quesito INT,
  IN p_titolo_test VARCHAR(255)
)
BEGIN
    SELECT *
    FROM Quesito_Riferisce_Tabella
    WHERE p_numero_quesito = numero_quesito AND p_titolo_test = titolo_test;
END;

DROP PROCEDURE IF EXISTS VISUALIZZAZIONE_OPZIONI;
CREATE PROCEDURE VISUALIZZAZIONE_OPZIONI(
  IN p_titolo_test VARCHAR(255),
  IN p_numero_quesito INT
)
BEGIN
	SELECT numerazione, campo_testo, corretta
	FROM Opzione
	WHERE p_titolo_test = titolo_test AND p_numero_quesito=numero_quesito;
END;

DROP PROCEDURE IF EXISTS VISUALIZZAZIONE_SOLUZIONE_CODICE;
CREATE PROCEDURE VISUALIZZAZIONE_SOLUZIONE_CODICE(
  IN p_titolo_test VARCHAR(255),
  IN p_numero_quesito INT
)
BEGIN
	SELECT numerazione, codice
	FROM Soluzione_Codice
	WHERE titolo_test = p_titolo_test AND numero_quesito=p_numero_quesito;
END;

DROP PROCEDURE IF EXISTS MODIFICA_VISUALIZZA_RISPOSTE;
CREATE PROCEDURE MODIFICA_VISUALIZZA_RISPOSTE(
  IN p_titolo_test VARCHAR(255),
  IN p_nuovo_valore BOOLEAN
)  
BEGIN
	UPDATE Test
  SET visualizza_risposte = p_nuovo_valore
  WHERE titolo = p_titolo_test;
END;


DROP PROCEDURE IF EXISTS INSERIMENTO_MESSAGGIO_DOCENTE;
CREATE PROCEDURE INSERIMENTO_MESSAGGIO_DOCENTE(
  IN p_data_messaggio DATE,
  IN p_titolo VARCHAR(225),
  IN p_testo TEXT,
  IN p_email_docente VARCHAR(255),
  IN p_titolo_test VARCHAR(255)
)
BEGIN
	INSERT INTO Messaggio_Docente(data_messaggio, titolo, testo, email_docente, titolo_test)
  VALUES (p_data_messaggio, p_titolo, p_testo, p_email_docente, p_titolo_test);
END;


DROP PROCEDURE IF EXISTS INSERIMENTO_MESSAGGIO_STUDENTE;
CREATE PROCEDURE INSERIMENTO_MESSAGGIO_STUDENTE(
  IN p_data_messaggio DATE,
  IN p_titolo VARCHAR(225),
  IN p_testo TEXT,
  IN p_email_studente VARCHAR(255),
  IN p_email_docente VARCHAR(255),
  IN p_titolo_test VARCHAR(255)
)
BEGIN
	INSERT INTO Messaggio_Studente(data_messaggio, titolo, testo, email_studente, email_docente, titolo_test)
  VALUES (p_data_messaggio, p_titolo, p_testo, p_email_studente, p_email_docente, p_titolo_test);
END;


DROP PROCEDURE IF EXISTS VISUALIZZA_MESSAGGI_INVIATI_DOCENTE;
CREATE PROCEDURE VISUALIZZA_MESSAGGI_INVIATI_DOCENTE(
  IN p_email_docente VARCHAR(255)
)
BEGIN
		SELECT *
    FROM Messaggio_Docente
    WHERE email_docente = p_email_docente;
END;


DROP PROCEDURE IF EXISTS VISUALIZZA_MESSAGGI_RICEVUTI_DOCENTE;
CREATE PROCEDURE VISUALIZZA_MESSAGGI_RICEVUTI_DOCENTE(
  IN p_email_docente VARCHAR(255)
)
BEGIN
		SELECT *
    FROM Messaggio_Studente
    WHERE email_docente = p_email_docente;
END;


DROP PROCEDURE IF EXISTS VISUALIZZA_MESSAGGI_INVIATI_STUDENTE;
CREATE PROCEDURE VISUALIZZA_MESSAGGI_INVIATI_STUDENTE(
  IN p_email_studente VARCHAR(255)
)
BEGIN
		SELECT *
    FROM Messaggio_Studente
    WHERE email_studente = p_email_studente;
END;


DROP PROCEDURE IF EXISTS VISUALIZZA_MESSAGGI_RICEVUTI_STUDENTE;
CREATE PROCEDURE VISUALIZZA_MESSAGGI_RICEVUTI_STUDENTE(
  IN p_email_studente VARCHAR(255)
)
BEGIN
    SELECT titolo, testo, data_messaggio, email_docente, titolo_test
    FROM Messaggio_Docente;
END;


DROP PROCEDURE IF EXISTS INSERISCI_O_AGGIORNA_RISPOSTA_CHIUSA;
CREATE PROCEDURE INSERISCI_O_AGGIORNA_RISPOSTA_CHIUSA(
  IN p_esito BOOLEAN,
  IN p_email_studente VARCHAR(255),
  IN p_numero_quesito INT,
  IN p_numero_opzione INT,
  IN p_titolo_test VARCHAR(255)
)
BEGIN
    DECLARE existing_id INT;

    SELECT id INTO existing_id FROM RispostaChiusa 
    WHERE email_studente = p_email_studente 
      AND numero_quesito = p_numero_quesito 
      AND titolo_test = p_titolo_test;

    IF existing_id IS NOT NULL THEN
        UPDATE RispostaChiusa 
        SET esito = p_esito, numero_opzione = p_numero_opzione 
        WHERE id = existing_id;
    ELSE
        INSERT INTO RispostaChiusa (esito, email_studente, numero_quesito, numero_opzione, titolo_test)
        VALUES (p_esito, p_email_studente, p_numero_quesito, p_numero_opzione, p_titolo_test);
    END IF;
END;


DROP PROCEDURE IF EXISTS VERIFICA_OPZIONE_CORRETTA;
CREATE PROCEDURE VERIFICA_OPZIONE_CORRETTA(
    IN p_numero_quesito INT,
    IN p_numero_opzione INT,
    IN p_titolo_test VARCHAR(255),
    OUT p_corretta BOOLEAN
)
BEGIN
    SELECT corretta INTO p_corretta
    FROM Opzione
    WHERE numero_quesito = p_numero_quesito
      AND numerazione = p_numero_opzione
      AND titolo_test = p_titolo_test;
END;


DROP PROCEDURE IF EXISTS INSERISCI_O_AGGIORNA_RISPOSTA_CODICE;
CREATE PROCEDURE INSERISCI_O_AGGIORNA_RISPOSTA_CODICE(
    IN p_esito BOOLEAN,
    IN p_emailStudente VARCHAR(255),
    IN p_numeroQuesito INT,
    IN p_sqlTesto TEXT,
    IN p_titoloTest VARCHAR(255)
)
BEGIN
    DECLARE rispostaEsistente INT;

    SELECT id INTO rispostaEsistente
    FROM RispostaCodice
    WHERE email_studente = p_emailStudente AND numero_quesito = p_numeroQuesito AND titolo_test = p_titoloTest;

    IF rispostaEsistente IS NOT NULL THEN
        UPDATE RispostaCodice
        SET esito = p_esito, testoSQL = p_sqlTesto
        WHERE id = rispostaEsistente;
    ELSE
        INSERT INTO RispostaCodice (esito, email_studente, numero_quesito, testoSQL, titolo_test)
        VALUES (p_esito, p_emailStudente, p_numeroQuesito, p_sqlTesto, p_titoloTest);
    END IF;
END;


DROP PROCEDURE IF EXISTS INSERISCI_SVOLGIMENTO_TEST;
CREATE PROCEDURE INSERISCI_SVOLGIMENTO_TEST(
  IN p_data_ultima_risposta DATE,
  IN p_data_prima_risposta DATE,
  IN p_email_studente VARCHAR(255),
  IN p_titolo_test VARCHAR(255),
  IN p_stato ENUM('Aperto', 'InCompletamento', 'Concluso')
)
BEGIN
    INSERT INTO Studente_Svolge_Test(p_data_ultima_risposta, p_data_prima_risposta, p_email_studente, p_titolo_test, p_stato)
    VALUES (data_ultima_risposta, data_prima_risposta, email_studente, titolo_test, stato);
END;


DROP PROCEDURE IF EXISTS CALCOLA_ESITO_TEST;
CREATE PROCEDURE CALCOLA_ESITO_TEST(
    IN p_email_studente VARCHAR(255), 
    IN p_titolo_test VARCHAR(255)
)
BEGIN
    DECLARE p_quesiti_totali INT DEFAULT 0;
    DECLARE p_risposte_chiuse_corrette INT DEFAULT 0;
    DECLARE p_risposte_codice_corrette INT DEFAULT 0;
    DECLARE p_quesiti_corretti INT DEFAULT 0;
    
    SELECT COUNT(*) INTO p_quesiti_totali
    FROM Quesito
    WHERE titolo_test = p_titolo_test;
    
    SELECT COUNT(*) INTO p_risposte_chiuse_corrette
    FROM RispostaChiusa
    WHERE email_studente = p_email_studente
      AND titolo_test = p_titolo_test
      AND esito = TRUE;

    SELECT COUNT(*) INTO p_risposte_codice_corrette
    FROM RispostaCodice
    WHERE email_studente = p_email_studente
      AND titolo_test = p_titolo_test
      AND esito = TRUE;

    SET p_quesiti_corretti = p_risposte_chiuse_corrette + p_risposte_codice_corrette;

    INSERT INTO TestCompleti (email_studente, titolo_test, punteggio, totale)
    VALUES (p_email_studente, p_titolo_test, p_quesiti_corretti, p_quesiti_totali)
    ON DUPLICATE KEY UPDATE 
        punteggio = p_quesiti_corretti,
        totale = p_quesiti_totali;
END;


DROP PROCEDURE IF EXISTS VISUALIZZA_TEST_NON_COMPLETI;
CREATE PROCEDURE VISUALIZZA_TEST_NON_COMPLETI(
    IN p_email_docente VARCHAR(255),
    IN p_email_studente VARCHAR(255)
)
BEGIN
    SELECT t.titolo, t.data_creazione
    FROM test t
    LEFT JOIN studente_svolge_test sst 
        ON t.titolo = sst.titolo_test 
        AND sst.email_studente = p_email_studente 
        AND sst.stato = 'Concluso'
    WHERE t.email_docente = p_email_docente 
    AND sst.titolo_test IS NULL; 
END;



DROP PROCEDURE IF EXISTS AGGIORNA_DATI_TEST;
CREATE PROCEDURE AGGIORNA_DATI_TEST(
    IN p_email_studente VARCHAR(255),
    IN p_titolo_test VARCHAR(255)
)
BEGIN
    DECLARE v_exists INT;

    SELECT COUNT(*) INTO v_exists
    FROM Studente_Svolge_Test
    WHERE email_studente = p_email_studente AND titolo_test = p_titolo_test;

    IF v_exists = 0 THEN
        INSERT INTO Studente_Svolge_Test (email_studente, titolo_test, data_prima_risposta, data_ultima_risposta, stato)
        VALUES (p_email_studente, p_titolo_test, NOW(), NOW(), 'InCompletamento');
    ELSE
        UPDATE Studente_Svolge_Test
        SET data_ultima_risposta = NOW()
        WHERE email_studente = p_email_studente AND titolo_test = p_titolo_test;
    END IF;
END;



DROP PROCEDURE IF EXISTS VISUALIZZA_ESITI;
CREATE PROCEDURE VISUALIZZA_ESITI(
    IN p_email_studente VARCHAR(255)
)
BEGIN
    SELECT *
    FROM TestCompleti
    WHERE email_studente = p_email_studente;
END;



DROP PROCEDURE IF EXISTS VISUALIZZA_OPZIONE_SCELTA;
CREATE PROCEDURE VISUALIZZA_OPZIONE_SCELTA(
    IN p_email_studente VARCHAR(255),
    IN p_titolo_test VARCHAR(255),
    IN p_numero_quesito INT,
    OUT p_campo_testo VARCHAR(255)
)
BEGIN
    DECLARE numero_opzione_scelta INT;

    SELECT numero_opzione INTO numero_opzione_scelta
    FROM RispostaChiusa
    WHERE email_studente = p_email_studente 
      AND titolo_test = p_titolo_test 
      AND numero_quesito = p_numero_quesito;

    SELECT campo_testo INTO p_campo_testo
    FROM Opzione
    WHERE titolo_test = p_titolo_test 
      AND numero_quesito = p_numero_quesito 
      AND numerazione = numero_opzione_scelta;
END;



DROP PROCEDURE IF EXISTS VISUALIZZA_TESTO_INSERITO;
CREATE PROCEDURE VISUALIZZA_TESTO_INSERITO(
    IN p_email_studente VARCHAR(255),
    IN p_titolo_test VARCHAR(255),
    IN p_numero_quesito INT,
    OUT p_testoSQL VARCHAR(255)
)
BEGIN
    SELECT testoSQL INTO p_testoSQL
    FROM RispostaCodice
    WHERE titolo_test = p_titolo_test 
      AND numero_quesito = p_numero_quesito 
      AND email_studente = p_email_studente;
END;


DROP PROCEDURE IF EXISTS AGGIORNA_VISUALIZZAZIONE_RISPOSTE;
CREATE PROCEDURE AGGIORNA_VISUALIZZAZIONE_RISPOSTE(
    IN p_titolo_test VARCHAR(255),
    IN p_email_docente VARCHAR(255),
    IN p_new_state BOOLEAN
)
BEGIN
    UPDATE Test 
    SET visualizza_risposte = p_new_state 
    WHERE titolo = p_titolo_test AND email_docente = p_email_docente;
END;









/* **************************************************** TRIGGERS **********************************************************************************/


CREATE TRIGGER set_numero_progressivo_quesito
BEFORE INSERT ON Quesito
FOR EACH ROW
BEGIN
    DECLARE max_numero INT;
    
    SELECT IFNULL(MAX(numero_progressivo), 0) INTO max_numero
    FROM Quesito
    WHERE titolo_test = NEW.titolo_test;
    
    SET NEW.numero_progressivo = max_numero + 1;
END;


CREATE TRIGGER set_numero_progressivo_opzione
BEFORE INSERT ON Opzione
FOR EACH ROW
BEGIN
    DECLARE max_numero INT;
    
    SELECT IFNULL(MAX(numerazione), 0) INTO max_numero
    FROM Opzione
    WHERE numero_quesito = NEW.numero_quesito
          AND titolo_test = NEW.titolo_test;
    
    SET NEW.numerazione = max_numero + 1;
END;

CREATE TRIGGER set_numero_progressivo_soluzione_codice
BEFORE INSERT ON Soluzione_Codice
FOR EACH ROW
BEGIN
    DECLARE max_numero INT;
    
    SELECT IFNULL(MAX(numerazione), 0) INTO max_numero
    FROM Soluzione_Codice
    WHERE numero_quesito = NEW.numero_quesito
          AND titolo_test = NEW.titolo_test;
    
    SET NEW.numerazione = max_numero + 1;
END;


CREATE TRIGGER cambio_stato_test_concluso_docente
AFTER UPDATE ON Test
FOR EACH ROW
BEGIN
    declare nstudentesvolgetest int;
    IF NEW.visualizza_risposte = TRUE THEN
        UPDATE studente_svolge_test
        SET stato = 'concluso'
        WHERE titolo_test = NEW.titolo;
    END IF;
END;

CREATE TRIGGER after_insert_risposta
AFTER INSERT ON RispostaChiusa
FOR EACH ROW
BEGIN
    UPDATE Quesito
    SET num_risposte = num_risposte + 1
    WHERE numero_progressivo = NEW.numero_quesito
    AND titolo_test = NEW.titolo_test;
END;

CREATE TRIGGER after_insert_risposta_codice
AFTER INSERT ON RispostaCodice
FOR EACH ROW
BEGIN
    -- Aggiorna il campo num_risposte nella tabella Quesito per i quesiti di codice
    UPDATE Quesito
    SET num_risposte = num_risposte + 1
    WHERE numero_progressivo = NEW.numero_quesito
    AND titolo_test = NEW.titolo_test;
END;


CREATE TRIGGER incrementa_num_righe
AFTER INSERT ON TabellaAppoggio
FOR EACH ROW
BEGIN
    DECLARE tabella_nome VARCHAR(255);

    -- Assegna il nome della tabella dalla riga appena inserita
    SET tabella_nome = NEW.nome_tabella;

    -- Aggiorna il campo num_righe nella TabellaSQL per la tabella specificata
    UPDATE TabellaSQL
    SET num_righe = num_righe + 1
    WHERE nome_tabella = tabella_nome;
END;



CREATE TRIGGER tr_after_insert_risposta_chiusa
AFTER INSERT ON RispostaChiusa
FOR EACH ROW
BEGIN
    DECLARE total_questions INT;
    DECLARE answered_chiusa INT;
    DECLARE answered_codice INT;
    DECLARE answered_questions INT;
    
    
    -- Chiama la stored procedure per aggiornare le date nella tabella Studente_Svolge_Test
    CALL AGGIORNA_DATI_TEST(NEW.email_studente, NEW.titolo_test);

    -- Conta il numero totale di domande per il test
    SELECT COUNT(*) INTO total_questions
    FROM Quesito
    WHERE titolo_test = NEW.titolo_test;

    -- Conta il numero di domande risposte correttamente per RispostaChiusa
    SELECT COUNT(*) INTO answered_chiusa
    FROM RispostaChiusa
    WHERE email_studente = NEW.email_studente AND titolo_test = NEW.titolo_test AND esito = TRUE;

    -- Conta il numero di domande risposte correttamente per RispostaCodice
    SELECT COUNT(*) INTO answered_codice
    FROM RispostaCodice
    WHERE email_studente = NEW.email_studente AND titolo_test = NEW.titolo_test AND esito = TRUE;

    -- Somma le risposte corrette da entrambe le tabelle
    SET answered_questions = answered_chiusa + answered_codice;

    -- Se tutte le domande sono state risposte correttamente, aggiorna lo stato del test
    IF answered_questions = total_questions THEN
        UPDATE Studente_Svolge_Test
        SET stato = 'Concluso'
        WHERE email_studente = NEW.email_studente AND titolo_test = NEW.titolo_test;
        
        -- Chiama la stored procedure CALCOLA_ESITO_TEST per aggiornare i risultati
        CALL CALCOLA_ESITO_TEST(NEW.email_studente, NEW.titolo_test);
    END IF;
END;


CREATE TRIGGER tr_after_insert_risposta_codice
AFTER INSERT ON RispostaCodice
FOR EACH ROW
BEGIN
		DECLARE total_questions INT;
    DECLARE answered_chiusa INT;
    DECLARE answered_codice INT;
    DECLARE answered_questions INT;
    
    -- Chiama la stored procedure per aggiornare le date nella tabella Studente_Svolge_Test
    CALL AGGIORNA_DATI_TEST(NEW.email_studente, NEW.titolo_test);

    -- Conta il numero totale di domande per il test
    SELECT COUNT(*) INTO total_questions
    FROM Quesito
    WHERE titolo_test = NEW.titolo_test;

    -- Conta il numero di domande risposte correttamente per RispostaChiusa
    SELECT COUNT(*) INTO answered_chiusa
    FROM RispostaChiusa
    WHERE email_studente = NEW.email_studente AND titolo_test = NEW.titolo_test AND esito = TRUE;

    -- Conta il numero di domande risposte correttamente per RispostaCodice
    SELECT COUNT(*) INTO answered_codice
    FROM RispostaCodice
    WHERE email_studente = NEW.email_studente AND titolo_test = NEW.titolo_test AND esito = TRUE;

    -- Somma le risposte corrette da entrambe le tabelle
    SET answered_questions = answered_chiusa + answered_codice;

    -- Se tutte le domande sono state risposte correttamente, aggiorna lo stato del test
    IF answered_questions = total_questions THEN
        UPDATE Studente_Svolge_Test
        SET stato = 'Concluso'
        WHERE email_studente = NEW.email_studente AND titolo_test = NEW.titolo_test;
        
        -- Chiama la stored procedure CALCOLA_ESITO_TEST per aggiornare i risultati
        CALL CALCOLA_ESITO_TEST(NEW.email_studente, NEW.titolo_test);
    END IF;
END;


CREATE TRIGGER tr_after_update_risposta_chiusa
AFTER UPDATE ON RispostaChiusa
FOR EACH ROW
BEGIN
    DECLARE total_questions INT;
    DECLARE answered_chiusa INT;
    DECLARE answered_codice INT;
    DECLARE answered_questions INT;

    -- Conta il numero totale di domande per il test
    SELECT COUNT(*) INTO total_questions
    FROM Quesito
    WHERE titolo_test = NEW.titolo_test;

    -- Conta il numero di domande risposte correttamente per RispostaChiusa
    SELECT COUNT(*) INTO answered_chiusa
    FROM RispostaChiusa
    WHERE email_studente = NEW.email_studente AND titolo_test = NEW.titolo_test AND esito = TRUE;

    -- Conta il numero di domande risposte correttamente per RispostaCodice
    SELECT COUNT(*) INTO answered_codice
    FROM RispostaCodice
    WHERE email_studente = NEW.email_studente AND titolo_test = NEW.titolo_test AND esito = TRUE;

    -- Somma le risposte corrette da entrambe le tabelle
    SET answered_questions = answered_chiusa + answered_codice;

    -- Se tutte le domande sono state risposte correttamente, aggiorna lo stato del test
    IF answered_questions = total_questions THEN
        UPDATE Studente_Svolge_Test
        SET stato = 'Concluso'
        WHERE email_studente = NEW.email_studente AND titolo_test = NEW.titolo_test;
        
        -- Chiama la stored procedure CALCOLA_ESITO_TEST per aggiornare i risultati
        CALL CALCOLA_ESITO_TEST(NEW.email_studente, NEW.titolo_test);
    END IF;
END;


CREATE TRIGGER tr_after_update_risposta_codice
AFTER UPDATE ON RispostaCodice
FOR EACH ROW
BEGIN
    DECLARE total_questions INT;
    DECLARE answered_questions INT;
    DECLARE answered_chiusa INT;
    DECLARE answered_codice INT;

    -- Conta il numero totale di domande per il test
    SELECT COUNT(*) INTO total_questions
    FROM Quesito
    WHERE titolo_test = NEW.titolo_test;

    -- Conta il numero di domande risposte correttamente per RispostaChiusa
    SELECT COUNT(*) INTO answered_chiusa
    FROM RispostaChiusa
    WHERE email_studente = NEW.email_studente AND titolo_test = NEW.titolo_test AND esito = TRUE;

    -- Conta il numero di domande risposte correttamente per RispostaCodice
    SELECT COUNT(*) INTO answered_codice
    FROM RispostaCodice
    WHERE email_studente = NEW.email_studente AND titolo_test = NEW.titolo_test AND esito = TRUE;

    -- Somma le risposte corrette da entrambe le tabelle
    SET answered_questions = answered_chiusa + answered_codice;

    -- Se tutte le domande sono state risposte correttamente, aggiorna lo stato del test
    IF answered_questions = total_questions THEN
        UPDATE Studente_Svolge_Test
        SET stato = 'Concluso'
        WHERE email_studente = NEW.email_studente AND titolo_test = NEW.titolo_test;
        
        -- Chiama la stored procedure CALCOLA_ESITO_TEST per aggiornare i risultati
        CALL CALCOLA_ESITO_TEST(NEW.email_studente, NEW.titolo_test);
    END IF;
END;
    
    
CREATE TRIGGER tr_after_update_visualizza_risposte
AFTER UPDATE ON Test
FOR EACH ROW
BEGIN
    -- Verifica se il campo visualizza_risposte è stato modificato da FALSE a TRUE
    IF OLD.visualizza_risposte = FALSE AND NEW.visualizza_risposte = TRUE THEN
        -- Aggiorna lo stato del test a 'Concluso' per tutti gli studenti
        UPDATE Studente_Svolge_Test
        SET stato = 'Concluso'
        WHERE titolo_test = NEW.titolo;
    END IF;
END;
