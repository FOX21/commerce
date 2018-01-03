<?php

namespace craft\commerce\base;

/**
 * Plan trait
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since  2.0
 */
trait PlanTrait
{
    // Properties
    // =========================================================================

    /**
     * @var int Payment source ID
     */
    public $id;

    /**
     * @var int The gateway ID.
     */
    public $gatewayId;

    /**
     * @var string plan name
     */
    public $name;

    /**
     * @var string plan handle
     */
    public $handle;

    /**
     * @var string plan reference on the gateway
     */
    public $reference;

    /**
     * @var bool whether the plan is enabled on site
     */
    public $enabled;

    /**
     * @var bool whether the plan is archived
     */
    public $isArchived;

    /**
     * @var \DateTime when the plan was archived
     */
    public $dateArchived;

    /**
     * @var string gateway response
     */
    public $response;
}
