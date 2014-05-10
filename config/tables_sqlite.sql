create table if not exists CONFIG_SERVICES (
    ID integer primary key,
    NAME varchar(255),
    SERVICE_TEST_ID integer
)
/

create table if not exists SERVICE_TEST_STATUSES (
    ID integer primary key,
    SERVICE_TEST_ID integer,
    RUN_DATE timestamp,
    STATUS varchar(100),
    RESPONSE_TIME bigint,
    unique (SERVICE_TEST_ID asc, RUN_DATE desc)
)
/

create table if not exists SERVICE_TESTS (
    ID integer primary key,
    NAME varchar (256),
    PATH varchar (2048),
    unique (NAME asc)
)
/
