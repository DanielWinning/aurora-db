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

        return sprintf('<span title="Database Queries">%s (%d) %sms</span>', $svg, count($this->queries), $this->getTotalExecutionTime());
    }

    /**
     * @return false|string
     */
    public function getPanel(): false|string
    {
        $html = '<div><h1>Database Queries</h1>';

        if (!count($this->queries)) {
            $html .= '<div style="border: none; background-color: #fff; color: #ebebeb; font-size: 1.25rem; text-align: center; font-style: italic">No queries were ran during this request</div>';
        } else {
            foreach ($this->queries as [$query, $params, $time]) {
                $queryHtml = file_get_contents(dirname(__DIR__, 2) . '/assets/query.html');
                $queryHtml = str_replace('%query%', $query, $queryHtml);
                $queryHtml = str_replace('%params%', $this->getParametersHtml($params), $queryHtml);

                $timeInMilliseconds = number_format($time * 1000, 2);
                $queryHtml = str_replace('%time%', htmlspecialchars($timeInMilliseconds) . 'ms', $queryHtml);

                $html .= $queryHtml;
            }
        }

        return $html . '</div>';
    }

    /**
     * @param array $params
     *
     * @return string
     */
    private function getParametersHtml(array $params): string
    {
        $html = '';

        foreach ($params as $param => $value) {
            $html .= sprintf('<span style="display: block; color: cornflowerblue"><strong style="color: darkorange">%s</strong>: %s</span>', $param, $value);
        }

        return $html;
    }

    /**
     * @return string
     */
    private function getTotalExecutionTime(): string
    {
        if (!count($this->queries)) {
            return number_format(0, 2);
        }

        $totalTimeTaken = 0;

        foreach ($this->queries as $query) {
            $totalTimeTaken += ($query * 1000);
        }

        return number_format($totalTimeTaken, 2);
    }
}