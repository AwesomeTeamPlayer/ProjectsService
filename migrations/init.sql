CREATE TABLE projects (
    id VARCHAR(10) NOT NULL,
    name VARCHAR(100) NOT NULL,
    type INT NOT NULL,
    is_archived BOOL NOT NULL DEFAULT false,
    created_at DATETIME NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE projects_users (
    project_id VARCHAR(10) NOT NULL,
    user_id VARCHAR(10) NOT NULL
);

CREATE UNIQUE INDEX projects_users_unique_index
ON projects_users (project_id, user_id);
