-- $CVSHeader: phpauthorization/ezpdo/libs/adodb/session/adodb-sessions.oracle.clob.sql,v 1.1 2009/06/17 15:23:54 viktor Exp $

DROP TABLE adodb_sessions;

CREATE TABLE sessions (
	sesskey		CHAR(32)	DEFAULT '' NOT NULL,
	expiry		INT		DEFAULT 0 NOT NULL,
	expireref	VARCHAR(64)	DEFAULT '',
	data		CLOB		DEFAULT '',
	PRIMARY KEY	(sesskey)
);

CREATE INDEX ix_expiry ON sessions (expiry);

QUIT;
