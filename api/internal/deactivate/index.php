<?php

define("ROOTDIR", "../../../");
define("REAL_ROOTDIR", "../../../");

require_once REAL_ROOTDIR."includes/Controller.php";
use \Catalyst\API\{Endpoint, ErrorCodes, Response};
use \Catalyst\Database\{Column, DeleteQuery, JoinClause, SelectQuery, Tables, UpdateQuery, WhereClause};
use \Catalyst\HTTPCode;
use \Catalyst\Form\FormRepository;
use \Catalyst\User\User;

Endpoint::init(true, 1);

FormRepository::getDeactivateForm()->checkServerSide();

if (strtolower($_POST["username"]) != strtolower($_SESSION["user"]->getUsername())) {
	HTTPCode::set(400);
	Response::sendErrorResponse(90602, ErrorCodes::ERR_90602);
}

$userId = $_SESSION["user"]->getId();

$query = new SelectQuery();
$query->setTable(Tables::USERS);

$query->addColumn(new Column("HASHED_PASSWORD", Tables::USERS));

$whereClause = new WhereClause();
$whereClause->addToClause([new Column("ID", Tables::USERS), "=", $userId]);
$query->addAdditionalCapability($whereClause);

$query->execute();

$result = $query->getResult();

if (!password_verify($_POST["password"], $result[0]["HASHED_PASSWORD"])) {
	HTTPCode::set(400);
	Response::sendErrorResponse(90604, ErrorCodes::ERR_90604);
}

$deactivateUserQuery = new UpdateQuery();
$deactivateUserQuery->setTable(Tables::USERS);
$deactivateUserQuery->addColumn(new Column("DEACTIVATED", Tables::USERS));
$deactivateUserQuery->addValue(true);
$whereClause = new WhereClause();
$whereClause->addToClause([new Column("ID", Tables::USERS), "=", $userId]);
$deactivateUserQuery->addAdditionalCapability($whereClause);
$deactivateUserQuery->execute();

$removeApiAuthorizationsQuery = new DeleteQuery();
$removeApiAuthorizationsQuery->setTable(Tables::API_AUTHORIZATIONS);
$whereClause = new WhereClause();
$whereClause->addToClause([new Column("USER_ID", Tables::API_AUTHORIZATIONS), "=", $userId]);
$removeApiAuthorizationsQuery->addAdditionalCapability($whereClause);
$removeApiAuthorizationsQuery->execute();

$removeApiKeysQuery = new DeleteQuery();
$removeApiKeysQuery->setTable(Tables::API_KEYS);
$whereClause = new WhereClause();
$whereClause->addToClause([new Column("USER_ID", Tables::API_KEYS), "=", $userId]);
$removeApiKeysQuery->addAdditionalCapability($whereClause);
$removeApiKeysQuery->execute();

if ($_SESSION["user"]->isArtist()) {
	$artistId = $_SESSION["user"]->getArtistPageId();

	$deleteArtistQuery = new UpdateQuery();
	$deleteArtistQuery->setTable(Tables::ARTIST_PAGES);
	$deleteArtistQuery->addColumn(new Column("DELETED", Tables::ARTIST_PAGES));
	$deleteArtistQuery->addValue(true);
	$whereClause = new WhereClause();
	$whereClause->addToClause([new Column("USER_ID", Tables::ARTIST_PAGES), "=", $userId]);
	$deleteArtistQuery->addAdditionalCapability($whereClause);
	$deleteArtistQuery->execute();

	$removeArtistSocialMediaQuery = new DeleteQuery();
	$removeArtistSocialMediaQuery->setTable(Tables::ARTIST_SOCIAL_MEDIA);
	$whereClause = new WhereClause();
	$whereClause->addToClause([new Column("ARTIST_ID", Tables::ARTIST_SOCIAL_MEDIA), "=", $artistId]);
	$removeArtistSocialMediaQuery->addAdditionalCapability($whereClause);
	$removeArtistSocialMediaQuery->execute();

	$removeArtistStreamingIntegrationsQuery = new DeleteQuery();
	$removeArtistStreamingIntegrationsQuery->setTable(Tables::ARTIST_STREAMING_INTEGRATIONS);
	$whereClause = new WhereClause();
	$whereClause->addToClause([new Column("ARTIST_ID", Tables::ARTIST_STREAMING_INTEGRATIONS), "=", $artistId]);
	$removeArtistStreamingIntegrationsQuery->addAdditionalCapability($whereClause);
	$removeArtistStreamingIntegrationsQuery->execute();

	$archiveCommissionsQuery = new UpdateQuery();
	$archiveCommissionsQuery->setTable(Tables::COMMISSIONS);
	$archiveCommissionsQuery->addColumn(new Column("ARCHIVED_ARTIST", Tables::COMMISSIONS));
	$archiveCommissionsQuery->addValue(true);
	$joinClause = new JoinClause();
	$joinClause->setType(JoinClause::INNER);
	$joinClause->setJoinTable(Tables::COMMISSION_TYPES);
	$joinClause->setLeftColumn(new Column("COMMISSION_TYPE_ID", Tables::COMMISSIONS));
	$joinClause->setRightColumn(new Column("ID", Tables::COMMISSION_TYPES));
	$whereClause = new WhereClause();
	$whereClause->addToClause([new Column("ARTIST_PAGE_ID", Tables::COMMISSION_TYPES), "=", $artistId]);
	$archiveCommissionsQuery->addAdditionalCapability($whereClause);
	$archiveCommissionsQuery->execute();

	$deleteCommissionTypeImages = new DeleteQuery();
	$deleteCommissionTypeImages->setTable(Tables::COMMISSION_TYPE_IMAGES);
	$joinClause = new JoinClause();
	$joinClause->setType(JoinClause::INNER);
	$joinClause->setJoinTable(Tables::COMMISSION_TYPES);
	$joinClause->setLeftColumn(new Column("COMMISSION_TYPE_ID", Tables::COMMISSION_TYPE_IMAGES));
	$joinClause->setRightColumn(new Column("ID", Tables::COMMISSION_TYPES));
	$whereClause = new WhereClause();
	$whereClause->addToClause([new Column("ARTIST_PAGE_ID", Tables::COMMISSION_TYPES), "=", $artistId]);
	$deleteCommissionTypeImages->addAdditionalCapability($whereClause);
	$deleteCommissionTypeImages->execute();

	$deleteCommissionTypeModifiers = new UpdateQuery();
	$deleteCommissionTypeModifiers->setTable(Tables::COMMISSION_TYPE_MODIFIERS);
	$deleteCommissionTypeModifiers->addColumn(new Column("DELETED", Tables::COMMISSION_TYPE_MODIFIERS));
	$deleteCommissionTypeModifiers->addValue(true);
	$joinClause = new JoinClause();
	$joinClause->setType(JoinClause::INNER);
	$joinClause->setJoinTable(Tables::COMMISSION_TYPES);
	$joinClause->setLeftColumn(new Column("COMMISSION_TYPE_ID", Tables::COMMISSION_TYPE_MODIFIERS));
	$joinClause->setRightColumn(new Column("ID", Tables::COMMISSION_TYPES));
	$whereClause = new WhereClause();
	$whereClause->addToClause([new Column("ARTIST_PAGE_ID", Tables::COMMISSION_TYPES), "=", $artistId]);
	$deleteCommissionTypeModifiers->addAdditionalCapability($whereClause);
	$deleteCommissionTypeModifiers->execute();

	$deleteCommissionTypePaymentOptions = new UpdateQuery();
	$deleteCommissionTypePaymentOptions->setTable(Tables::COMMISSION_TYPE_PAYMENT_OPTIONS);
	$deleteCommissionTypePaymentOptions->addColumn(new Column("DELETED", Tables::COMMISSION_TYPE_PAYMENT_OPTIONS));
	$deleteCommissionTypePaymentOptions->addValue(true);
	$joinClause = new JoinClause();
	$joinClause->setType(JoinClause::INNER);
	$joinClause->setJoinTable(Tables::COMMISSION_TYPES);
	$joinClause->setLeftColumn(new Column("COMMISSION_TYPE_ID", Tables::COMMISSION_TYPE_PAYMENT_OPTIONS));
	$joinClause->setRightColumn(new Column("ID", Tables::COMMISSION_TYPES));
	$whereClause = new WhereClause();
	$whereClause->addToClause([new Column("ARTIST_PAGE_ID", Tables::COMMISSION_TYPES), "=", $artistId]);
	$deleteCommissionTypePaymentOptions->addAdditionalCapability($whereClause);
	$deleteCommissionTypePaymentOptions->execute();

}
$_SESSION = [];

Response::sendSuccessResponse("Success");