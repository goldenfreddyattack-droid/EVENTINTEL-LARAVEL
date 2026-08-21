<?php
require_once __DIR__ . '/../config/db.php';
header('Content-Type: application/json');

$pdo = db();

function normalizeServiceCategory($service) {
    $service = trim((string) $service);
    if ($service === '') {
        return null;
    }

    $map = [
        'venue' => 'Venue',
        'catering' => 'Catering',
        'catering/food' => 'Catering',
        'food' => 'Catering',
        'host' => 'Host',
        'host/mc' => 'Host',
        'mc' => 'Host',
        'sounds' => 'Sounds & Lights',
        'sounds & lights' => 'Sounds & Lights',
        'lights' => 'Sounds & Lights',
        'photographer' => 'Photographer',
        'photo' => 'Photographer',
        'clothing' => 'Clothing',
        'attire' => 'Clothing',
        'clothing/attire' => 'Clothing',
        'decorations' => 'Decorations',
        'decor' => 'Decorations'
    ];

    $key = strtolower($service);
    return $map[$key] ?? $service;
}

function getBudgetTarget($budget, $serviceCategory) {
    $share = match ($serviceCategory) {
        'Venue' => 0.30,
        'Catering' => 0.35,
        'Host' => 0.08,
        'Photographer' => 0.10,
        'Sounds & Lights' => 0.08,
        'Clothing' => 0.05,
        'Decorations' => 0.04,
        default => 0.10,
    };

    return max(1000, round($budget * $share));
}

function getSupplierRecommendations($pdo, $services, $budget, $pax) {
    $serviceCategories = array_values(array_unique(array_filter(array_map('normalizeServiceCategory', (array) $services))));
    if (empty($serviceCategories)) {
        return [];
    }

    $recommendations = [];
    foreach ($serviceCategories as $serviceCategory) {
        if ($serviceCategory === 'Decorations') {
            continue;
        }

        $targetBudget = getBudgetTarget($budget, $serviceCategory);
        $stmt = $pdo->prepare(
            "SELECT name, category, price, address, rating FROM supplier_services WHERE category = ? AND price IS NOT NULL AND price > 0 ORDER BY price ASC, rating DESC LIMIT 6"
        );
        $stmt->execute([$serviceCategory]);
        $items = $stmt->fetchAll();

        foreach ($items as $item) {
            $price = (float) ($item['price'] ?? 0);
            if ($price <= 0) {
                continue;
            }

            $withinBudget = $price <= $targetBudget * 1.25;
            if (!$withinBudget && $budget > 0 && $price > $budget) {
                continue;
            }

            $score = ($item['rating'] ?? 0) * 100 - ($price / 10);
            if ($pax > 0 && $serviceCategory === 'Venue' && $price > 0) {
                $score -= max(0, $pax - 100) * 0.3;
            }

            $recommendations[] = [
                'name' => $item['name'],
                'category' => $item['category'],
                'price' => $price,
                'address' => $item['address'] ?: 'Address available on supplier profile',
                'rating' => (float) ($item['rating'] ?? 0),
                'score' => $score,
            ];
        }
    }

    if (empty($recommendations)) {
        $stmt = $pdo->prepare(
            "SELECT name, category, price, address, rating FROM supplier_services WHERE price IS NOT NULL AND price > 0 ORDER BY price ASC, rating DESC LIMIT 6"
        );
        $stmt->execute();
        $recommendations = array_map(function ($item) {
            return [
                'name' => $item['name'],
                'category' => $item['category'],
                'price' => (float) ($item['price'] ?? 0),
                'address' => $item['address'] ?: 'Address available on supplier profile',
                'rating' => (float) ($item['rating'] ?? 0),
                'score' => ((float) ($item['rating'] ?? 0) * 100) - (((float) ($item['price'] ?? 0)) / 10),
            ];
        }, $stmt->fetchAll());
    }

    usort($recommendations, function ($a, $b) {
        if ($a['score'] === $b['score']) {
            return $a['price'] <=> $b['price'];
        }
        return $b['score'] <=> $a['score'];
    });

    return array_slice($recommendations, 0, 6);
}

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$event = $in['event'] ?? 'Event';
$budget = (int)($in['budget'] ?? 0);
$pax = (int)($in['pax'] ?? 0);
$services = $in['services'] ?? [];
$eventId = $in['eventId'] ?? null;
$regenerate = $in['regenerate'] ?? false;

// Event flow templates by type
$eventFlows = [
    'wedding' => [
        ['time' => '08:00 AM', 'activity' => 'Guest Arrival & Registration', 'prep' => 'Venue preparation, Coat check'],
        ['time' => '09:00 AM', 'activity' => 'Ceremony Starts', 'prep' => 'Bride entrance, vows, rings'],
        ['time' => '10:00 AM', 'activity' => 'Reception & Cocktail Hour', 'prep' => 'Photos, mingling, appetizers'],
        ['time' => '11:30 AM', 'activity' => 'Grand Entrance & First Dance', 'prep' => 'Music cues, lighting effects'],
        ['time' => '12:00 PM', 'activity' => 'Lunch Service', 'prep' => 'Multi-course meal service'],
        ['time' => '01:00 PM', 'activity' => 'Toasts & Speeches', 'prep' => 'Best man, bridesmaids, parents'],
        ['time' => '02:00 PM', 'activity' => 'Cake Cutting & Entertainment', 'prep' => 'Music, dancing, photo booth'],
        ['time' => '04:00 PM', 'activity' => 'Evening Activities & Dessert', 'prep' => 'DJ transitions, special dances'],
        ['time' => '06:00 PM', 'activity' => 'Farewell & Send-off', 'prep' => 'Guest departure arrangements'],
    ],
    'birthday' => [
        ['time' => '02:00 PM', 'activity' => 'Guest Arrival', 'prep' => 'Welcome drinks, games setup'],
        ['time' => '02:30 PM', 'activity' => 'Icebreaker Activities & Games', 'prep' => 'Team games, music playing'],
        ['time' => '03:30 PM', 'activity' => 'Snack Break', 'prep' => 'Light appetizers, drinks'],
        ['time' => '04:00 PM', 'activity' => 'Main Activities & Entertainment', 'prep' => 'DJ performance, dancing'],
        ['time' => '05:00 PM', 'activity' => 'Dinner Service', 'prep' => 'Main course buffet or plated meal'],
        ['time' => '06:00 PM', 'activity' => 'Birthday Cake & Singing', 'prep' => 'Special lighting, candles'],
        ['time' => '06:30 PM', 'activity' => 'Gifts & Photos', 'prep' => 'Gift opening, group photos'],
        ['time' => '07:30 PM', 'activity' => 'Dessert & Closing Activities', 'prep' => 'Dessert service, farewells'],
    ],
    'debut' => [
        ['time' => '03:00 PM', 'activity' => 'Guest Arrival & Registration', 'prep' => 'Welcome drinks, guest book signing'],
        ['time' => '04:00 PM', 'activity' => 'Entrance & 18 Roses', 'prep' => 'Debutante entrance, 18 roses presentation'],
        ['time' => '04:30 PM', 'activity' => '18 Candles & Gifts', 'prep' => 'Candle lighting, gifts from 18 candles'],
        ['time' => '05:30 PM', 'activity' => 'Dinner Service', 'prep' => 'Main course service, toasts'],
        ['time' => '06:30 PM', 'activity' => 'Debutante Speech & Thank You', 'prep' => 'Speech, parent appreciation'],
        ['time' => '07:00 PM', 'activity' => 'Dance & Entertainment', 'prep' => 'First dance, party games, music'],
        ['time' => '08:00 PM', 'activity' => 'Cake Cutting & Celebration', 'prep' => 'Cake, photo sessions'],
        ['time' => '09:00 PM', 'activity' => 'Party Continues & Farewell', 'prep' => 'Dancing, guest departure'],
    ],
    'corporate' => [
        ['time' => '08:00 AM', 'activity' => 'Registration & Breakfast', 'prep' => 'Coffee, pastries, name badges'],
        ['time' => '09:00 AM', 'activity' => 'Opening Remarks', 'prep' => 'CEO/Director presentation'],
        ['time' => '09:30 AM', 'activity' => 'Keynote Speech', 'prep' => 'Main speaker presentation'],
        ['time' => '10:30 AM', 'activity' => 'Break & Networking', 'prep' => 'Refreshments, mingling'],
        ['time' => '11:00 AM', 'activity' => 'Breakout Sessions', 'prep' => 'Panel discussions, workshops'],
        ['time' => '12:00 PM', 'activity' => 'Lunch', 'prep' => 'Catered meal, table seating'],
        ['time' => '01:00 PM', 'activity' => 'Awards & Recognition', 'prep' => 'Recognition ceremony'],
        ['time' => '02:00 PM', 'activity' => 'Networking & Team Building', 'prep' => 'Games, activities, mingling'],
        ['time' => '04:00 PM', 'activity' => 'Closing Remarks & Departure', 'prep' => 'Thank you speech, farewell'],
    ],
    'graduation' => [
        ['time' => '09:00 AM', 'activity' => 'Guest Arrival & Seating', 'prep' => 'Program distribution, marshaling'],
        ['time' => '10:00 AM', 'activity' => 'Processional & Opening Ceremony', 'prep' => 'National anthem, prayers'],
        ['time' => '10:30 AM', 'activity' => 'Principal\'s Address', 'prep' => 'Opening remarks'],
        ['time' => '11:00 AM', 'activity' => 'Academic Presentations', 'prep' => 'Department heads, faculty speeches'],
        ['time' => '12:00 PM', 'activity' => 'Diploma Distribution', 'prep' => 'Graduates walking stage'],
        ['time' => '01:00 PM', 'activity' => 'Lunch Reception', 'prep' => 'Catered buffet, mingling'],
        ['time' => '02:00 PM', 'activity' => 'Photo Session & Celebrations', 'prep' => 'Family photos, celebrations'],
        ['time' => '04:00 PM', 'activity' => 'Farewell & Departure', 'prep' => 'Goodbyes, direction home'],
    ],
];

// Get event type flow
$eventType = strtolower($event);
$flow = null;

foreach ($eventFlows as $type => $timeline) {
    if (strpos($eventType, $type) !== false) {
        $flow = $timeline;
        break;
    }
}

// Default flow if no match
if (!$flow) {
    $flow = [
        ['time' => '09:00 AM', 'activity' => 'Event Start & Guest Arrival', 'prep' => 'Registration, welcome drinks'],
        ['time' => '10:00 AM', 'activity' => 'Opening Program', 'prep' => 'Opening remarks, introductions'],
        ['time' => '11:00 AM', 'activity' => 'Main Activities', 'prep' => 'Core event activities'],
        ['time' => '12:00 PM', 'activity' => 'Lunch Service', 'prep' => 'Food service to guests'],
        ['time' => '01:00 PM', 'activity' => 'Afternoon Program', 'prep' => 'Continued activities, entertainment'],
        ['time' => '03:00 PM', 'activity' => 'Snack & Break', 'prep' => 'Refreshment time'],
        ['time' => '04:00 PM', 'activity' => 'Closing Program', 'prep' => 'Final remarks, group photos'],
        ['time' => '05:00 PM', 'activity' => 'Farewell & Departure', 'prep' => 'Thank you, guest exit'],
    ];
}

// Build timeline HTML
$timelineHtml = '<h4 style="color: #f3c547; margin-top: 15px; margin-bottom: 10px;">📅 Recommended Event Timeline:</h4>';
foreach ($flow as $item) {
    $timelineHtml .= '<div class="timeline-item">';
    $timelineHtml .= '<div class="timeline-time">' . esc($item['time']) . '</div>';
    $timelineHtml .= '<div class="timeline-event"><strong>' . esc($item['activity']) . '</strong><br><small style="color: #999;">' . esc($item['prep']) . '</small></div>';
    $timelineHtml .= '</div>';
}

// Budget breakdown
$perPersonBudget = $pax > 0 ? round($budget / $pax, 2) : 0;
$budgetHtml = '<h4 style="color: #f3c547; margin-top: 20px; margin-bottom: 10px;">💰 Budget Breakdown:</h4>';
$budgetHtml .= '<p><strong>Total Budget:</strong> ₱' . number_format($budget, 2) . ' | <strong>Per Person Budget:</strong> ₱' . number_format($perPersonBudget, 2) . '</p>';

// Visual budget distribution across all service categories
$allocationMap = [
    'Catering' => 0.35,
    'Venue' => 0.30,
    'Photographer' => 0.10,
    'Host' => 0.08,
    'Sounds & Lights' => 0.08,
    'Clothing' => 0.05,
    'Decorations' => 0.04,
];
$budgetHtml .= '<div style="margin-top: 14px;">';
$budgetHtml .= '<div style="display: flex; flex-wrap: wrap; gap: 10px;">';
foreach ($allocationMap as $cat => $share) {
    $amount = round($budget * $share);
    $pctShow = round($share * 100);
    $color = ['#d4a017', '#e5b93c', '#f0c94d', '#b8860b', '#e8b70f', '#c99208', '#dfb53a'];
    static $i = 0;
    $c = $color[$i % count($color)];
    $i++;
    $budgetHtml .= '<div style="flex: 1 1 145px; background: #fff; border: 1px solid rgba(212,160,23,0.18); border-radius: 12px; padding: 12px 14px;">';
    $budgetHtml .= '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; font-size:13px; font-weight:700; color:#333;">';
    $budgetHtml .= '<span>' . esc($cat) . '</span><span style="color:#d4a017;">' . $pctShow . '%</span></div>';
    $budgetHtml .= '<div style="height:8px; border-radius:999px; background:rgba(212,160,23,.12); overflow:hidden;">';
    $budgetHtml .= '<div style="height:100%; width:' . $pctShow . '%; border-radius:999px; background:' . $c . ';"></div></div>';
    $budgetHtml .= '<div style="margin-top:8px; font-size:14px; font-weight:800; color:#111;">₱' . number_format($amount) . '</div>';
    $budgetHtml .= '</div>';
}
$budgetHtml .= '</div></div>';

// Service recommendations based on budget allocation
$serviceHtml = '<h4 style="color: #f3c547; margin-top: 20px; margin-bottom: 10px;">🎯 Budget Allocation & Supplier Picks:</h4>';

$recommendations = [];
if (in_array('Venue', $services)) {
    $venueAlloc = round($budget * 0.30);
    $recommendations[] = '<div class="service-recommendation">🏛️ <strong>Venue Rental:</strong> ₱' . number_format($venueAlloc) . ' (30% of budget) - Good target for a venue that fits your guest count.</div>';
}
if (in_array('Catering', $services)) {
    $cateringAlloc = round($budget * 0.35);
    $recommendations[] = '<div class="service-recommendation">🍽️ <strong>Catering/Food:</strong> ₱' . number_format($cateringAlloc) . ' (35% of budget) - Aim for a caterer that keeps food costs within the guest count.</div>';
}
if (in_array('Host', $services)) {
    $hostAlloc = round($budget * 0.08);
    $recommendations[] = '<div class="service-recommendation">🎤 <strong>Host/MC:</strong> ₱' . number_format($hostAlloc) . ' (8% of budget) - A professional host helps the flow of the event stay smooth.</div>';
}
if (in_array('Photographer', $services)) {
    $photoAlloc = round($budget * 0.10);
    $recommendations[] = '<div class="service-recommendation">📸 <strong>Photography:</strong> ₱' . number_format($photoAlloc) . ' (10% of budget) - Budget for coverage and edited deliverables.</div>';
}
if (in_array('Sounds', $services)) {
    $soundAlloc = round($budget * 0.08);
    $recommendations[] = '<div class="service-recommendation">🎵 <strong>Sounds & Lights:</strong> ₱' . number_format($soundAlloc) . ' (8% of budget) - Good lighting and sound can make the event feel more polished.</div>';
}
if (in_array('Clothing', $services)) {
    $clothingAlloc = round($budget * 0.05);
    $recommendations[] = '<div class="service-recommendation">👔 <strong>Attire:</strong> ₱' . number_format($clothingAlloc) . ' (5% of budget) - Keep attire styling affordable and elegant.</div>';
}
if (in_array('Decorations', $services)) {
    $decorAlloc = round($budget * 0.04);
    $recommendations[] = '<div class="service-recommendation">🎨 <strong>Decorations:</strong> ₱' . number_format($decorAlloc) . ' (4% of budget) - Decorative styling should stay within the overall plan.</div>';
}

$supplierRecommendations = getSupplierRecommendations($pdo, $services, $budget, $pax);
if (!empty($supplierRecommendations)) {
    $recommendations[] = '<div class="service-recommendation" style="background: rgba(243,197,71,0.10); border-color: rgba(243,197,71,0.35);"><strong>🏪 Best supplier matches for your budget:</strong></div>';
    foreach ($supplierRecommendations as $index => $supplier) {
        $badge = $index === 0 ? ' <span style="color:#f3c547; font-weight:700;">Best fit</span>' : '';
        $recommendations[] = '<div class="service-recommendation">' . $badge . ' <strong>' . esc($supplier['name']) . '</strong><br><small>' . esc($supplier['category']) . ' • ₱' . number_format($supplier['price'], 2) . ' • Rating ' . number_format($supplier['rating'], 1) . '</small><br><small>' . esc($supplier['address']) . '</small></div>';
    }
} else {
    $recommendations[] = '<div class="service-recommendation">No matching supplier records were found yet for this service selection.</div>';
}

if (empty($recommendations)) {
    $recommendations[] = '<div class="service-recommendation">Select services to get budget allocation recommendations</div>';
}

$serviceHtml .= implode('', $recommendations);

// AI Enhancement via OpenAI if available
$aiNotes = '';
if (OPENAI_API_KEY && !$regenerate) {
    $prompt = "Create a brief (3-4 sentences) creative event planning tip for a $event with " . $pax . " guests and ₱" . number_format($budget) . " budget. Include one unique suggestion.";
    $payload = ["model" => "gpt-4o-mini", "messages" => [["role" => "user", "content" => $prompt]]];

    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json", "Authorization: Bearer " . OPENAI_API_KEY],
        CURLOPT_POSTFIELDS => json_encode($payload)
    ]);

    $raw = curl_exec($ch);
    $json = json_decode($raw, true);
    $tip = $json['choices'][0]['message']['content'] ?? '';

    if ($tip) {
        $aiNotes = '<h4 style="color: #f3c547; margin-top: 20px; margin-bottom: 10px;">💡 AI Planning Tips:</h4>';
        $aiNotes .= '<p style="background: rgba(243,197,71,0.08); padding: 12px; border-radius: 8px; border-left: 3px solid #f3c547; color: #333;">' . esc($tip) . '</p>';
    }
}

$html = '<div style="color: #333;">';
$html .= '<p><strong>Event Type:</strong> ' . esc($event) . ' | <strong>Guests:</strong> ' . $pax . ' | <strong>Budget:</strong> ₱' . number_format($budget, 2) . '</p>';
$html .= $timelineHtml;
$html .= $budgetHtml;
$html .= $serviceHtml;
$html .= $aiNotes;
$html .= '</div>';

echo json_encode(["html" => $html]);
?>
