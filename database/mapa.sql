-- Cria o banco de dados
CREATE DATABASE IF NOT EXISTS mapa DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Usando o banco de dados
USE mapa;

-- CRIANDO A TABELA DE USUARIOS
CREATE TABLE IF NOT EXISTS usuarios (
    codigo INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(50),
    usuario         VARCHAR(15),
    senha           VARCHAR(32),
    email           VARCHAR(80),
    data_criacao    DATETIME DEFAULT now(),
    status          CHAR(2) DEFAULT ''
);

-- CRIANDO A TABELA DE CADASTRO DAS SALAS
CREATE TABLE IF NOT EXISTS salas (
    codigo          INT PRIMARY KEY,
    descricao       VARCHAR(30) DEFAULT '',
    andar           INT,
    capacidade      INT,
    data_criacao    DATETIME DEFAULT now(),
    status          CHAR(2) DEFAULT ''
);

-- CRIANDO A TABELA DE CADASTRO DE PROFESSORES
CREATE TABLE IF NOT EXISTS professores (
    codigo          INT AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(30) DEFAULT '',
    cpf             VARCHAR(11) DEFAULT '',
    tipo            CHAR(1) DEFAULT 'F',
    data_criacao    DATETIME DEFAULT now(),
    status          CHAR(2) DEFAULT ''
);

-- CRIANDO A TABELA DE CADASTRO DAS TURMAS
CREATE TABLE IF NOT EXISTS turmas(
    codigo          INT AUTO_INCREMENT PRIMARY KEY,
    descricao       VARCHAR(50) DEFAULT '',
    capacidade      INT DEFAULT 0,
    dt_inicio       DATE,
    data_criacao    DATETIME DEFAULT now(),
    status          CHAR(2) DEFAULT ''
);

-- CRIANDO A TABELA DE CADASTRO DOS HORARIOS
CREATE TABLE IF NOT EXISTS horarios(
    codigo          INT AUTO_INCREMENT PRIMARY KEY,
    descricao       VARCHAR(50) DEFAULT '',
    hora_inicial     TIME,
    hora_final        TIME,
    data_criacao    DATETIME DEFAULT now(),
    status          CHAR(2) DEFAULT ''
);

-- CRIANDO TABELA DE MAPEAMENTO DE SALAS
CREATE TABLE IF NOT EXISTS mapas(
	codigo          INT AUTO_INCREMENT PRIMARY KEY,
    dt_reserva      DATE,
	codigo_sala     INT DEFAULT 0,
    codigo_horario  INT DEFAULT 0,
    codigo_turma    INT DEFAULT 0,
    codigo_professor    INT DEFAULT 0,
    status              CHAR(2) DEFAULT '',
    
    FOREIGN KEY (codigo_sala) references salas(codigo),
    FOREIGN KEY (codigo_horario) references horarios(codigo),
    FOREIGN KEY (codigo_turma) references turmas(codigo),
    FOREIGN KEY (codigo_professor) references professores(codigo)
);

select * from salas;

