<?php

declare(strict_types=1);

namespace Phpml\Clustering\KMeans;

use Countable;
use IteratorAggregate;
use LogicException;
use SplObjectStorage;

class Cluster extends Point implements IteratorAggregate, Countable
{
    /**
     * @var Space
     */
    protected $space;

    /**
     * @var SplObjectStorage|Point[]
     */
    protected $points;

    public function __construct(Space $space, array $coordinates)
    {
        parent::__construct($coordinates);
        $this->space = $space;
        $this->points = new SplObjectStorage();
    }

    public function getPoints(): array
    {
        $points = [];
        foreach ($this->points as $point) {
            $points[] = $point->toArray();
        }

        return $points;
    }

    public function toArray(): array
    {
        return [
            'centroid' => parent::toArray(),
            'points' => $this->getPoints(),
        ];
    }

    public function attach(Point $point): Point
    {
        if ($point instanceof self) {
            throw new LogicException('cannot attach a cluster to another');
        }

        $this->points->attach($point);

        return $point;
    }

    public function detach(Point $point): Point
    {
        $this->points->detach($point);

        return $point;
    }

    public function attachAll(SplObjectStorage $points): void
    {
        $this->points->addAll($points);
    }

    public function detachAll(SplObjectStorage $points): void
    {
        $this->points->removeAll($points);
    }

    public function updateCentroid(): void
    {
        if (empty($this->points)) {
            return;
        }

        $centroid = $this->space->newPoint(array_fill(0, $this->dimension, 0));

        foreach ($this->points as $point) {
            for ($n = 0; $n < $this->dimension; ++$n) {
                $centroid->coordinates[$n] += $point->coordinates[$n];
            }
        }

        $count = count($this->points);
        for ($n = 0; $n < $this->dimension; ++$n) {
            $this->coordinates[$n] = $centroid->coordinates[$n] / $count;
        }
    }

    /**
     * @return Point[]|SplObjectStorage
     */
    public function getIterator()
    {
        return $this->points;
    }

    /**
     * @return mixed
     */
    public function count()
    {
        return count($this->points);
    }

    public function setCoordinates(array $newCoordinates): void
    {
        $this->coordinates = $newCoordinates;
    }
}
