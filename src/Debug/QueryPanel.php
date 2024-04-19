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
        $svg = file_get_contents(dirname(__DIR__, 2) . '/assets/db.svg');

        return sprintf('<span title="Database Queries">%s</span>', $svg);
    }

    /**
     * @return false|string
     */
    public function getPanel(): false|string
    {
        $html = '<div style="padding: .125rem">';

        foreach ($this->queries as [$query, $params, $time]) {
            $queryHtml = file_get_contents(dirname(__DIR__, 2) . '/assets/query.html');
            $queryHtml = str_replace('%query%', $query, $queryHtml);
            $queryHtml = str_replace('%params%', htmlspecialchars(print_r($params, true)), $queryHtml);
            $queryHtml = str_replace('%time%', htmlspecialchars($time), $queryHtml);

            $html .= $queryHtml;
        }

        return $html . '</div>';
    }
}