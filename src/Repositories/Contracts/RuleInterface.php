<?php

namespace Asahasrabuddhe\Repositories\Contracts;

use Asahasrabuddhe\Repositories\Rule\Rule;

/**
 * Interface RuleInterface.
 */
interface RuleInterface
{
    /**
     * @param bool $status
     * @return $this
     */
    public function skipRule($status = true);

    /**
     * @return mixed
     */
    public function getRule();

    /**
     * @param Rule $criteria
     * @return $this
     */
    public function getByRule(Rule $criteria);

    /**
     * @param Rule $criteria
     * @return $this
     */
    public function pushRule(Rule $criteria);

    /**
     * @return $this
     */
    public function applyRule();
}
