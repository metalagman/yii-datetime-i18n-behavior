<?php

/**
 * @author Alexey Samoylov <alexey.samoylov@gmail.com>
 */
class DateTimeI18NBehavior extends CActiveRecordBehavior
{
    const DATE = 'date';
    const TIME = 'time';
    const DATETIME = 'datetime';

    public $attributes;
    public $exceptAttributes;

    protected $formats = [
        self::DATE => ['yyyy-MM-dd'],
        self::TIME => ['hh:mm:ss'],
        self::DATETIME => ['yyyy-MM-dd hh:mm:ss'],
    ];

    /**
     * @inheritdoc
     */
    public function beforeSave($event)
    {
        $this->initLocale();

        foreach ($event->sender->tableSchema->columns as $columnName => $column) {
            if ($this->applicable($columnName, $column->dbType, $event->sender->$columnName)) {
                $event->sender->$columnName = Yii::app()->dateFormatter->format($this->formats[$column->dbType][0],
                    CDateTimeParser::parse($event->sender->$columnName, $this->formats[$column->dbType][1])
                );
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function afterFind($event)
    {
        $this->initLocale();

        foreach ($event->sender->tableSchema->columns as $columnName => $column) {
            if ($this->applicable($columnName, $column->dbType, $event->sender->$columnName)) {
                $event->sender->$columnName = Yii::app()->dateFormatter->format($this->formats[$column->dbType][1],
                    CDateTimeParser::parse($event->sender->$columnName, $this->formats[$column->dbType][0])
                );
            }
        }
    }

    protected function initLocale()
    {
        $this->formats[self::DATE][] = Yii::app()->locale->dateFormat;
        $this->formats[self::TIME][] = Yii::app()->locale->timeFormat;
        $this->formats[self::DATETIME][] = Yii::app()->locale->dateTimeFormat;
    }

    /**
     * @param string $columnName
     * @param string $columnType
     * @param mixed $columnValue
     * @return bool
     */
    protected function applicable($columnName, $columnType, $columnValue)
    {
        return !(
            !is_string($columnValue)
            || strlen($columnValue) == 0
            || !array_key_exists($columnType, $this->formats)
            || is_array($this->attributes) && !in_array($columnName, $this->attributes)
            || is_array($this->exceptAttributes) && in_array($columnName, $this->exceptAttributes)
        );
    }
}