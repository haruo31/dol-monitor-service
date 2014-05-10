create table if not exists config_services (
    id integer primary key auto_increment,
    name varchar(255),
    service_test_id integer
)
/

create table if not exists service_test_statuses (
    id integer primary key auto_increment,
    service_test_id integer,
    run_date timestamp,
    status varchar(100),
    response_time bigint,
    unique (service_test_id asc, run_date desc)
)
/

create table if not exists service_tests (
    id integer primary key auto_increment,
    name varchar (256),
    path varchar (2048),
    unique (name asc)
)
/
