<?php
/**
 * Chart Helper
 * Provides helper functions for Chart.js integration
 * 
 * @author Kewer Development Team
 * @version 1.0.0
 */

/**
 * Generate Chart.js configuration for line chart
 */
function getLineChartConfig($data, $labels, $options = []) {
    $defaultOptions = [
        'responsive' => true,
        'maintainAspectRatio' => false,
        'plugins' => [
            'legend' => [
                'position' => 'top',
            ],
            'tooltip' => [
                'mode' => 'index',
                'intersect' => false,
            ]
        ],
        'scales' => [
            'y' => [
                'beginAtZero' => true,
                'ticks' => [
                    'callback' => 'js:function(value) { return "Rp " + value.toLocaleString("id-ID"); }'
                ]
            ]
        ]
    ];
    
    $options = array_merge_recursive($defaultOptions, $options);
    
    return [
        'type' => 'line',
        'data' => [
            'labels' => $labels,
            'datasets' => $data
        ],
        'options' => $options
    ];
}

/**
 * Generate Chart.js configuration for bar chart
 */
function getBarChartConfig($data, $labels, $options = []) {
    $defaultOptions = [
        'responsive' => true,
        'maintainAspectRatio' => false,
        'plugins' => [
            'legend' => [
                'position' => 'top',
            ]
        ],
        'scales' => [
            'y' => [
                'beginAtZero' => true,
                'ticks' => [
                    'callback' => 'js:function(value) { return "Rp " + value.toLocaleString("id-ID"); }'
                ]
            ]
        ]
    ];
    
    $options = array_merge_recursive($defaultOptions, $options);
    
    return [
        'type' => 'bar',
        'data' => [
            'labels' => $labels,
            'datasets' => $data
        ],
        'options' => $options
    ];
}

/**
 * Generate Chart.js configuration for pie chart
 */
function getPieChartConfig($data, $labels, $options = []) {
    $defaultOptions = [
        'responsive' => true,
        'maintainAspectRatio' => false,
        'plugins' => [
            'legend' => [
                'position' => 'right',
            ]
        ]
    ];
    
    $options = array_merge_recursive($defaultOptions, $options);
    
    return [
        'type' => 'pie',
        'data' => [
            'labels' => $labels,
            'datasets' => $data
        ],
        'options' => $options
    ];
}

/**
 * Generate Chart.js configuration for doughnut chart
 */
function getDoughnutChartConfig($data, $labels, $options = []) {
    $defaultOptions = [
        'responsive' => true,
        'maintainAspectRatio' => false,
        'plugins' => [
            'legend' => [
                'position' => 'right',
            ]
        ]
    ];
    
    $options = array_merge_recursive($defaultOptions, $options);
    
    return [
        'type' => 'doughnut',
        'data' => [
            'labels' => $labels,
            'datasets' => $data
        ],
        'options' => $options
    ];
}

/**
 * Generate color palette for charts
 */
function getChartColors($count) {
    $colors = [
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 99, 132, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(153, 102, 255, 0.8)',
        'rgba(255, 159, 64, 0.8)',
        'rgba(199, 199, 199, 0.8)',
        'rgba(83, 102, 255, 0.8)',
        'rgba(40, 159, 64, 0.8)',
        'rgba(210, 99, 132, 0.8)',
    ];
    
    $borderColors = [
        'rgba(54, 162, 235, 1)',
        'rgba(255, 99, 132, 1)',
        'rgba(255, 206, 86, 1)',
        'rgba(75, 192, 192, 1)',
        'rgba(153, 102, 255, 1)',
        'rgba(255, 159, 64, 1)',
        'rgba(199, 199, 199, 1)',
        'rgba(83, 102, 255, 1)',
        'rgba(40, 159, 64, 1)',
        'rgba(210, 99, 132, 1)',
    ];
    
    $result = ['background' => [], 'border' => []];
    
    for ($i = 0; $i < $count; $i++) {
        $result['background'][] = $colors[$i % count($colors)];
        $result['border'][] = $borderColors[$i % count($borderColors)];
    }
    
    return $result;
}
