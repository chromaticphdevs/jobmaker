drop table jobs;
CREATE TABLE jobs(
    id int(10) not null primary key auto_increment,
    token varchar(100),
    job_key varchar(100),
    table_name varchar(100),
    first_id int(10),
    last_id int(10),
    current_id int(10),
    total_records int(10),
    status varchar(50),
    zip_name text,
    zip_url text,
    processed_count int(10),
    created_at timestamp
);
