<?php

namespace AppBundle\Entity;

/**
 * @see https://law.stackexchange.com/questions/29190/gdpr-where-to-store-users-consent
 */
class OptinConsent
{
    private $id;
    private $createdAt;
    private $withdrawedAt;
}
