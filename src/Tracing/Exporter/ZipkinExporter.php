<?php

declare(strict_types=1);

namespace OpenTelemetry\Tracing\Exporter;

use OpenTelemetry\Tracing\Exporter;
use OpenTelemetry\Tracing\Span;
use OpenTelemetry\Tracing\Tracer;

class ZipkinExporter extends Exporter
{
    private $endpoint;

    public function convert(Span $span) : array
    {
        $row = [
            'id' => $span->getSpanContext()->getSpanId(),
            'traceId' => $span->getSpanContext()->getTraceId(),
            'parentId' => $span->getParentSpanContext()
                ? $span->getParentSpanContext()->getSpanId()
                : null,
            'localEndpoint' => $this->getEndpoint(),
            'name' => $span->getName(),
            'timestamp' => (integer) round($span->getStart()*1000000),
            'duration' => (integer) round($span->getEnd()*1000000) - round($span->getStart()*1000000),
        ];

        foreach ($span->getAttributes() as $k => $v) {
            if (!array_key_exists('tags', $row)) {
                $row['tags'] = [];
            }
            $row['tags'][$k] = $v;
        }

        foreach ($span->getEvents() as $event) {
            if (!array_key_exists('annotations', $row)) {
                $row['annotations'] = [];
            }
            $row['annotations'][] = [
                'timestamp' => round($event->getTimestamp()*1000000),
                'value' => $event->getName(),
            ];
        }

        return $row;
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function setEndpoint(array $endpoint) : self
    {
        $this->endpoint = $endpoint;
        return $this;
    }
}