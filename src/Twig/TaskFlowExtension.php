<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\Markup;

class TaskFlowExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('time_ago', [$this, 'timeAgo']),
            new TwigFilter('priority_icon', [$this, 'priorityIcon']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('progress_bar', [$this, 'progressBar'], ['is_safe' => ['html']]),
        ];
    }

    public function timeAgo($date): string
    {
        if (!$date) return '';

        $now = new \DateTime();
        $diff = $now->diff($date);

        if ($diff->d > 0) return 'il y a '.$diff->d.' jours';
        if ($diff->h > 0) return 'il y a '.$diff->h.' heures';
        if ($diff->i > 0) return 'il y a '.$diff->i.' minutes';

        return 'à l\'instant';
    }

    public function priorityIcon(string $priority): string
    {
        return match($priority) {
            'basse' => '🔵',
            'moyenne' => '🟢',
            'haute' => '🟠',
            'urgente' => '🔴',
            default => '⚪'
        };
    }

    public function progressBar(int $percent): string
    {
        $color = 'danger';

        if ($percent > 75) $color = 'success';
        elseif ($percent > 50) $color = 'warning';
        elseif ($percent > 25) $color = 'orange';

        if ($color === 'orange') {
            $class = 'bg-warning';
        } else {
            $class = 'bg-'.$color;
        }

        return '
        <div class="progress">
            <div class="progress-bar '.$class.'" style="width: '.$percent.'%">
                '.$percent.'%
            </div>
        </div>
        ';
    }
}