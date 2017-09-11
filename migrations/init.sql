CREATE TABLE projects_users (
    id INT NOT NULL AUTO_INCREMENT
    name VARCHAR NOT NULL,
    type INT NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE projects_users (
    project_id INT NOT NULL,
    user_id INT NOT NULL
);

CREATE UNIQUE INDEX projects_users_unique_index
ON projects_users (project_id, user_id);
