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

CREATE TABLE posts(
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
    CONSTRAINT pk_posts PRIMARY KEY(id),
    CONSTRAINT fk_post_user FOREIGN KEY(user_id) REFERENCES users(id),
    CONSTRAINT fk_post_category FOREIGN KEY(category_id) REFERENCES categories(id)
)ENGINE=InnoDb;

CREATE TABLE user_seen_post (
    id                  int(255) auto_increment NOT NULL,
    user_id             int(255) NOT NULL,
    post_id             int(255) NOT NULL,
    CONSTRAINT pk_post_seen PRIMARY KEY(id),
    CONSTRAINT fk_user_seen_post FOREIGN KEY(user_id) REFERENCES users(id),
    CONSTRAINT fk_post_seen FOREIGN KEY(post_id) REFERENCES posts(id)
)ENGINE=InnoDb;

CREATE TABLE user_pending_post(
    id                  int(255) auto_increment NOT NULL,
    user_id             int(255) NOT NULL,
    post_id             int(255) NOT NULL,
    CONSTRAINT pk_post_pending PRIMARY KEY(id),
    CONSTRAINT fk_user_pending_post FOREIGN KEY(user_id) REFERENCES users(id),
    CONSTRAINT fk_post_pending FOREIGN KEY(post_id) REFERENCES posts(id)
)ENGINE=InnoDb;

CREATE TABLE user_favourite_post(
    id                  int(255) auto_increment NOT NULL,
    user_id             int(255) NOT NULL,
    post_id             int(255) NOT NULL,
    CONSTRAINT pk_post_favourite PRIMARY KEY(id),
    CONSTRAINT fk_user_favourite_post FOREIGN KEY(user_id) REFERENCES users(id),
    CONSTRAINT fk_post_favourite FOREIGN KEY(post_id) REFERENCES posts(id)
)ENGINE=InnoDb;

CREATE TABLE user_seeing_post (
    id                  int(255) auto_increment NOT NULL,
    user_id             int(255) NOT NULL,
    post_id             int(255) NOT NULL,
    CONSTRAINT pk_post_seeing PRIMARY KEY(id),
    CONSTRAINT fk_user_seeing_post FOREIGN KEY(user_id) REFERENCES users(id),
    CONSTRAINT fk_post_seeing FOREIGN KEY(post_id) REFERENCES posts(id)
)ENGINE=InnoDb;