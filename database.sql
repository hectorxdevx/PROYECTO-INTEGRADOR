create database server_monitor;
use server_monitor;
create table users(
id_usuario int primary key auto_increment,
nombre_usuario varchar(100) not null,
apellidos varchar(100) not null,
correo_electronico varchar(320) unique,
contrasena varchar(256) not null
);
create table rol(
id_rol tinyint primary key auto_increment,
nombre_rol varchar(50) not null
);

insert into rol (nombre_rol) values ('user');
insert into rol (nombre_rol) values ('admin');
ALTER TABLE users ADD COLUMN id_rol TINYINT NOT NULL DEFAULT 1;
ALTER TABLE users ADD CONSTRAINT fk_rol FOREIGN KEY (id_rol) REFERENCES rol(id_rol);
