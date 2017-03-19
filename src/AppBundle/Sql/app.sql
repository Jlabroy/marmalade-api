DROP TABLE users;
CREATE TABLE users (
  id int NOT NULL AUTO_INCREMENT,
  first_name text,
  email varchar(255),
  password text,
  PRIMARY KEY (id),
  UNIQUE (email)
);


DROP TABLE pages;
CREATE TABLE pages (
  id int NOT NULL AUTO_INCREMENT,
  user_id int NOT NULL,
  url text,
  title text,
  meta_description text,
  content text,
  PRIMARY KEY (id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

DROP TABLE users;
CREATE TABLE users (
  id SERIAL PRIMARY KEY,
  first_name text,
  email varchar(255),
  password text
);

DROP TABLE pages;
CREATE TABLE pages (
  id serial,
  user_id int NOT NULL REFERENCES users(id),
  url text,
  title text,
  meta_description text,
  content text
);