<?php

namespace Luma\AuroraDatabase\Debug;

use Tracy\IBarPanel;

class QueryPanel implements IBarPanel
{
    private array $queries = [];

    /**
     * @param string $query
     * @param array $params
     * @param float $time
     *
     * @return void
     */
    public function addQuery(string $query, array $params, float $time): void
    {
        $this->queries[] = [$query, $params, $time];
    }

    /**
     * @return string
     */
    public function getTab(): string
    {
        return '<span title="Database Queries">DB</span>';
    }

    /**
     * @return false|string
     */
    public function getPanel(): false|string
    {
        ob_start();

        foreach ($this->queries as [$query, $params, $time]) {
            echo htmlspecialchars($query), '<br>';
            echo 'Parameters: ', htmlspecialchars(print_r($params, true)), '<br>';
            echo 'Time: ', htmlspecialchars($time);
        }

        return ob_get_clean();
    }
}