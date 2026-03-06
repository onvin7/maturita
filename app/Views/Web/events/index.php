<div class="nadpis">
    <h1>Events</h1>
    <h2>Events</h2>
</div>

<div class="events-container">
    
    <div class="events-info-banner" style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); padding: 40px; border-radius: 12px; margin-bottom: 50px; text-align: center; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
        <i class="fas fa-info-circle" style="font-size: 48px; color: #e30613; margin-bottom: 20px;"></i>
        <h3 style="color: #fff; font-size: 24px; margin-bottom: 15px; font-family: 'Fira Sans Condensed', sans-serif;">Důležité oznámení</h3>
        <p style="color: #ccc; font-size: 18px; line-height: 1.6; max-width: 800px; margin: 0 auto;">
            Naše závody jsou nově pořádány pod hlavičkou našeho nového projektu <strong>Bav se sportem</strong>. 
            Veškeré infomace najdeš <a href="#" style="color: #e30613; text-decoration: underline; font-weight: bold;">zde</a>.
        </p>
    </div>

    <?php
    // Struktura závodů podle roku
    // Každý rok má jednotlivé závody a jeden seriál závodů
    $eventsByYear = [
        '2024' => [
            'individual' => [
                ['name' => 'cyklistickey', 'title' => 'Cyklistickey Race', 'date' => '19. 5. 2024', 'location' => 'Poběžovice', 'active' => true],
                ['name' => 'bezeckey', 'title' => 'Běžeckey Race', 'date' => '18. 5. 2024', 'location' => 'Poběžovice', 'active' => true],
                ['name' => 'mtb-challenge', 'title' => 'MTB Challenge', 'date' => '15. 6. 2024', 'location' => 'Brno', 'active' => false],
            ],
            'series' => [
                'title' => 'CYCLI kritérium 2024',
                'events' => [
                    ['name' => 'cup-1', 'title' => 'CYCLI kritérium - Závod 1', 'date' => '10. 3. 2024', 'location' => 'Praha', 'active' => false],
                    ['name' => 'cup-2', 'title' => 'CYCLI kritérium - Závod 2', 'date' => '14. 4. 2024', 'location' => 'Brno', 'active' => false],
                    ['name' => 'cup-3', 'title' => 'CYCLI kritérium - Závod 3', 'date' => '12. 5. 2024', 'location' => 'Ostrava', 'active' => false],
                    ['name' => 'cup-4', 'title' => 'CYCLI kritérium - Závod 4', 'date' => '9. 6. 2024', 'location' => 'Plzeň', 'active' => false],
                    ['name' => 'cup-5', 'title' => 'CYCLI kritérium - Závod 5', 'date' => '7. 7. 2024', 'location' => 'Liberec', 'active' => false],
                    ['name' => 'cup-6', 'title' => 'CYCLI kritérium - Finále', 'date' => '4. 8. 2024', 'location' => 'České Budějovice', 'active' => false],
                ]
            ]
        ],
        '2023' => [
            'individual' => [
                ['name' => 'cyklistickey', 'title' => 'Cyklistickey Race', 'date' => '21. 5. 2023', 'location' => 'Poběžovice', 'active' => false],
                ['name' => 'bezeckey', 'title' => 'Běžeckey Race', 'date' => '20. 5. 2023', 'location' => 'Poběžovice', 'active' => false],
                ['name' => 'mtb-challenge', 'title' => 'MTB Challenge', 'date' => '17. 6. 2023', 'location' => 'Brno', 'active' => false],
            ],
            'series' => [
                'title' => 'CYCLI kritérium 2023',
                'events' => [
                    ['name' => 'cup-1', 'title' => 'CYCLI kritérium - Závod 1', 'date' => '12. 3. 2023', 'location' => 'Praha', 'active' => false],
                    ['name' => 'cup-2', 'title' => 'CYCLI kritérium - Závod 2', 'date' => '16. 4. 2023', 'location' => 'Brno', 'active' => false],
                    ['name' => 'cup-3', 'title' => 'CYCLI kritérium - Závod 3', 'date' => '14. 5. 2023', 'location' => 'Ostrava', 'active' => false],
                    ['name' => 'cup-4', 'title' => 'CYCLI kritérium - Závod 4', 'date' => '11. 6. 2023', 'location' => 'Plzeň', 'active' => false],
                    ['name' => 'cup-5', 'title' => 'CYCLI kritérium - Závod 5', 'date' => '9. 7. 2023', 'location' => 'Liberec', 'active' => false],
                    ['name' => 'cup-6', 'title' => 'CYCLI kritérium - Finále', 'date' => '6. 8. 2023', 'location' => 'České Budějovice', 'active' => false],
                ]
            ]
        ],
        '2022' => [
            'individual' => [
                ['name' => 'cyklistickey', 'title' => 'Cyklistickey Race', 'date' => '22. 5. 2022', 'location' => 'Poběžovice', 'active' => false],
                ['name' => 'bezeckey', 'title' => 'Běžeckey Race', 'date' => '21. 5. 2022', 'location' => 'Poběžovice', 'active' => false],
                ['name' => 'mtb-challenge', 'title' => 'MTB Challenge', 'date' => '18. 6. 2022', 'location' => 'Brno', 'active' => false],
            ],
            'series' => [
                'title' => 'CYCLI kritérium 2022',
                'events' => [
                    ['name' => 'cup-1', 'title' => 'CYCLI kritérium - Závod 1', 'date' => '13. 3. 2022', 'location' => 'Praha', 'active' => false],
                    ['name' => 'cup-2', 'title' => 'CYCLI kritérium - Závod 2', 'date' => '17. 4. 2022', 'location' => 'Brno', 'active' => false],
                    ['name' => 'cup-3', 'title' => 'CYCLI kritérium - Závod 3', 'date' => '15. 5. 2022', 'location' => 'Ostrava', 'active' => false],
                    ['name' => 'cup-4', 'title' => 'CYCLI kritérium - Závod 4', 'date' => '12. 6. 2022', 'location' => 'Plzeň', 'active' => false],
                    ['name' => 'cup-5', 'title' => 'CYCLI kritérium - Závod 5', 'date' => '10. 7. 2022', 'location' => 'Liberec', 'active' => false],
                    ['name' => 'cup-6', 'title' => 'CYCLI kritérium - Finále', 'date' => '7. 8. 2022', 'location' => 'České Budějovice', 'active' => false],
                ]
            ]
        ],
    ];
    ?>

    <?php foreach ($eventsByYear as $year => $yearData): ?>
        <div class="year-section">
            <div class="year-header">
                <h2><?= htmlspecialchars($year) ?></h2>
            </div>

            <!-- Závody -->
            <div class="events-section">
                <h3 class="section-title">Závody</h3>
                <div class="events-list">
                    <?php foreach ($yearData['individual'] as $event): ?>
                        <div class="event-card <?= $event['active'] ? 'active' : 'inactive' ?>">
                            <div class="event-content">
                                <div class="event-info">
                                    <h4><?= htmlspecialchars($event['title']) ?></h4>
                                    <div class="event-details">
                                        <div class="event-detail">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span><?= htmlspecialchars($event['date']) ?></span>
                                        </div>
                                        <div class="event-detail">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?= htmlspecialchars($event['location']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="event-action">
                                    <?php if ($event['active']): ?>
                                        <a href="/events/<?= htmlspecialchars($year) ?>/<?= htmlspecialchars($event['name']) ?>" class="btn-event">
                                            <span>Zobrazit detail</span>
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="btn-event disabled">
                                            <span>Brzy</span>
                                            <i class="fas fa-clock"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Seriál závodů -->
            <div class="events-section series-section">
                <h3 class="section-title"><?= htmlspecialchars($yearData['series']['title']) ?></h3>
                <div class="events-list series-list">
                    <?php foreach ($yearData['series']['events'] as $event): ?>
                        <div class="event-card series-card <?= $event['active'] ? 'active' : 'inactive' ?>">
                            <div class="event-content">
                                <div class="event-info">
                                    <h4><?= htmlspecialchars($event['title']) ?></h4>
                                    <div class="event-details">
                                        <div class="event-detail">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span><?= htmlspecialchars($event['date']) ?></span>
                                        </div>
                                        <div class="event-detail">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?= htmlspecialchars($event['location']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="event-action">
                                    <?php if ($event['active']): ?>
                                        <a href="/events/<?= htmlspecialchars($year) ?>/<?= htmlspecialchars($event['name']) ?>" class="btn-event">
                                            <span>Zobrazit detail</span>
                                            <i class="fas fa-arrow-right"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="btn-event disabled">
                                            <span>Brzy</span>
                                            <i class="fas fa-clock"></i>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

