<?php

namespace App\Observers;

abstract class AbstractObserver
{
    protected $channel = 'event';
    protected $record = '';

    protected $message = [
        'deleted' => '%s -> deleted %s record',
        'created' => '%s -> created %s record',
        'updated' => '%s -> updated %s record',
    ];

    /**
     * AbstractObserver constructor.
     */
    public function __construct()
    {
        if (!$this->record) {
            $this->record = $this->channel;
        }
    }

    /**
     * Log data when model deleted event fire
     *
     * @author tolawho
     * @param $model
     */
    function deleted($model)
    {
        loggy()->write(
            $this->channel,
            sprintf($this->message['deleted'], auth()->user()->username, $this->record),
            $model->toArray()
        );
    }

    /**
     * Log data when model created event fire
     *
     * @author tolawho
     * @param $model
     */
    function created($model)
    {
        if (auth()->user()) {
            loggy()->write(
                $this->channel,
                sprintf($this->message['created'], auth()->user()->username, $this->record),
                $model->toArray()
            );
        }
    }

    /**
     * Log data when model updated event fire
     *
     * @author tolawho
     * @param $model
     */
    function updated($model)
    {
        if (auth()->user()) {
            $changes = [];
            foreach ($model->getDirty() as $key => $value) {
                $original = $model->getOriginal($key);
                if ($original == $value) {
                    continue;
                }
                $changes[$key] = [
                    'old' => $original,
                    'new' => $value,
                ];
            }
            if (count($changes) > 1) {
                loggy()->write(
                    $this->channel,
                    sprintf($this->message['updated'], auth()->user()->username, $this->record),
                    ['id' => $model->id, 'data' => $changes]
                );
            }
        }
    }
}
