CREATE DATABASE IF NOT EXISTS dbfilms;
USE dbfilms;

CREATE TABLE users(
    id                  int(255) auto_increment NOT NULL,
    name                varchar(50) NOT NULL,
    role                varchar(20),
    email               varchar(255) NOT NULL,
    password            varchar(255) NOT NULL,
    description         text,
    image               varchar(255),
    created_at          datetime DEFAULT NULL,
    updated_at          datetime DEFAULT NULL,
    remember_token      varchar(255),
    CONSTRAINT pk_users PRIMARY KEY(id)
)ENGINE=InnoDb;

CREATE TABLE categories(
    id                  int(255) auto_increment NOT NULL,
    name                varchar(50) NOT NULL, 
    created_at          datetime DEFAULT NULL,
    updated_at          datetime DEFAULT NULL,
    CONSTRAINT pk_categories PRIMARY KEY(id)
)ENGINE=InnoDb;

CREATE TABLE films(
    id                  int(255) auto_increment NOT NULL,
    user_id             int(255) NOT NULL,
    category_id         int(255) NOT NULL,
    title               varchar(255) NOT NULL,
    description         text NOT NULL,
    image               varchar(255),
    created_at          datetime DEFAULT NULL,
    updated_at          datetime DEFAULT NULL,
    rating              int(255) DEFAULT NULL,
    duration            int(255) DEFAULT NULL,
    times_seen          int(255) DEFAULT NULL,
    CONSTRAINT pk_films PRIMARY KEY(id),
    CONSTRAINT fk_film_user FOREIGN KEY(user_id) REFERENCES users(id),
    CONSTRAINT fk_film_category FOREIGN KEY(category_id) REFERENCES categories(id)
)ENGINE=InnoDb;

CREATE TABLE user_seen_film(
    id                  int(255) auto_increment NOT NULL,
    user_id             int(255) NOT NULL,
    film_id             int(255) NOT NULL,
    CONSTRAINT pk_film_seen PRIMARY KEY(id),
    CONSTRAINT fk_user_seen_film FOREIGN KEY(user_id) REFERENCES users(id),
    CONSTRAINT fk_film_seen FOREIGN KEY(film_id) REFERENCES films(id)
)ENGINE=InnoDb;

CREATE TABLE user_pending_film(
    id                  int(255) auto_increment NOT NULL,
    user_id             int(255) NOT NULL,
    film_id             int(255) NOT NULL,
    CONSTRAINT pk_film_pending PRIMARY KEY(id),
    CONSTRAINT fk_user_pending_film FOREIGN KEY(user_id) REFERENCES users(id),
    CONSTRAINT fk_film_pending FOREIGN KEY(film_id) REFERENCES films(id)
)ENGINE=InnoDb;

CREATE TABLE user_favourite_film(
    id                  int(255) auto_increment NOT NULL,
    user_id             int(255) NOT NULL,
    film_id             int(255) NOT NULL,
    CONSTRAINT pk_film_favourite PRIMARY KEY(id),
    CONSTRAINT fk_user_favourite_film FOREIGN KEY(user_id) REFERENCES users(id),
    CONSTRAINT fk_film_favourite FOREIGN KEY(film_id) REFERENCES films(id)
)ENGINE=InnoDb;