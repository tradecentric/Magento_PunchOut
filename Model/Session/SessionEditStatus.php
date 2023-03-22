<?php
declare(strict_types=1);

namespace Punchout2Go\Punchout\Model\Session;

/**
 * Class SessionModeSetup
 * @package Punchout2Go\Punchout\Model\Session
 */
class SessionEditStatus
{
    const NOT_EDITABLE = 0;
    const EDITABLE = 1;

    /**
     * @var array|string[]
     */
    protected $editableOperations = ['edit', 'inspect'];

    /**
     * SessionEditStatus constructor.
     * @param array $operations
     */
    public function __construct(array $operations = [])
    {
        $this->editableOperations = array_merge($this->editableOperations, $operations);
    }

    /**
     * @param array $data
     * @return int
     */
    public function getEditStatus(array $data = [])
    {
        if (!$data) {
            return static::NOT_EDITABLE;
        }
        if (isset($data['is_edit'])) {
            return (int) $data['is_edit'];
        }
        if (isset($data['operation']) && in_array($data['operation'], $this->editableOperations)) {
            return static::EDITABLE;
        }
        return static::NOT_EDITABLE;
    }
}
