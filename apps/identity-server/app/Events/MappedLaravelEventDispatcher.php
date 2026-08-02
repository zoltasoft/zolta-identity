<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Contracts\Events\Dispatcher as LaravelDispatcher;
use Psr\Log\LoggerInterface;
use Zolta\Cqrs\Laravel\Services\LaravelEventDispatcher;
use Zolta\Domain\Events\Contracts\EventInterface;

final class MappedLaravelEventDispatcher extends LaravelEventDispatcher
{
    /**
     * @param  array<class-string<EventInterface>, list<class-string<EventInterface>>>  $eventMap
     */
    public function __construct(
        LaravelDispatcher $laravelDispatcher,
        private readonly array $eventMap,
        private readonly ?LoggerInterface $logger = null,
    ) {
        parent::__construct($laravelDispatcher, $logger);
    }

    public function dispatch(EventInterface $event): void
    {
        parent::dispatch($event);

        foreach ($this->eventMap[$event::class] ?? [] as $eventClass) {
            $mappedEvent = $this->makeInfraEventInstance($eventClass, $event);

            if (! $mappedEvent instanceof EventInterface) {
                $this->logger?->warning('Unable to create mapped infrastructure event', [
                    'domain_event' => $event::class,
                    'infrastructure_event' => $eventClass,
                ]);

                continue;
            }

            parent::dispatch($mappedEvent);
        }
    }
}
