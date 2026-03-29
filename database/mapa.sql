-- Cria o banco de dados
CREATE DATABASE IF NOT EXISTS mapa DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Usando o banco de dados
use mapa;

-- CRIANDO A TABELA DE USUARIOS
CREATE TABLE IF NOT EXISTS usuarios (
	id_usuario INT not null auto_increment primary key,
	nome 	   varchar(50),
	usuario    varchar(15),
	senha      varchar(32),
	email      varchar(80),
	dt_criacao datetime default now(),
	estatus    char(01) default ''
);

-- CRIANDO A TABELA DE CADASTRO DAS SALAS
CREATE TABLE IF NOT EXISTS tbl_sala (
	codigo 		INT primary key,
    descricao	varchar(30) default '',
    andar		INT,
    capacidade	INT,
    dt_criacao  datetime default now(),
    estatus		char(01) default ''
);

-- CRIANDO A TABELA DE CADASTRO DE PROFESSORES
CREATE TABLE IF NOT EXISTS professores(
	codigo 		INT auto_increment primary key,
	nome		varchar(30) default '',
	cpf			varchar(11) default '',
	tipo		char(1) default 'F',
	dt_criacao	datetime default now(),
	estatus		char(01) default ''
);

-- CRIANDO A TABELA DE CADASTRO DAS TURMAS
CREATE TABLE IF NOT EXISTS turmas(
	codigo		INT auto_increment primary key,
    descricao	varchar(50) default '',
    capacidade	INT default 0,
    dt_inicio	date,
	dt_criacao	datetime default now(),
    estatus		char(01) default ''
);

-- CRIANDO A TABELA DE CADASTRO DOS HORARIOS
CREATE TABLE IF NOT EXISTS horarios(
	codigo		INT auto_increment primary key,
    descricao	varchar(50) default '',
    hora_inicio	time,
    hora_fim	time,
    dt_criacao	datetime default now(),
    estatus		char(010) default ''
);

-- CRIANDO TABELA DE MAPEAMENTO DE SALAS
CREATE TABLE IF NOT EXISTS mapas(
	codigo				INT auto_increment primary key,
    dt_reserva			date,
	codigo_sala         INT default 0,
    codigo_horario		INT default 0,
    codigo_turma		INT default 0,
    codigo_professor	INT default 0,
    estatus				char(01) default '',
    
    foreign key (codigo_sala) references tbl_sala(codigo),
    foreign key (codigo_horario) references horarios(codigo),
    foreign key (codigo_turma) references turmas(codigo),
    foreign key (codigo_professor) references professores(codigo)
);

select * from tbl_sala;



