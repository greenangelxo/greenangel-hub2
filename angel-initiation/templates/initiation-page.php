<!-- Angel Initiation Ceremony Template -->
<div class="angel-initiation-body">
    
    <div class="cosmic-bg" id="cosmic-bg"></div>
    
    <div class="initiation-container">
        <div class="angel-card" id="angel-card">
            
            <!-- Progress Bar -->
            <div class="progress-bar" id="progress-bar">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
            
            <!-- Step Counter -->
            <div class="step-counter" id="step-counter">
                <span class="current-step">1</span> / <span class="total-steps">4</span>
            </div>
            
            <!-- Step 1: Name Generator -->
            <div class="initiation-step" id="step-name-generator" style="display: none;">
                <div class="step-header">
                    <div class="step-avatar">
                        <!-- Emoji Narrator for this step -->
                        <div class="narrator-container" id="narrator-container">
                            <!-- Narrator elements will be created by JavaScript -->
                        </div>
                    </div>
                    <h2 class="step-title">Choose Your Stoner Name</h2>
                    <p class="step-subtitle">Let the cosmic dice reveal your true identity</p>
                </div>
                
                <div class="name-display" id="name-display">
                    <div class="generated-name" id="generated-name">Roll the dice to discover your name...</div>
                </div>
                
                <div class="dice-container" id="dice-container">
                    <div class="dice" id="dice-1">🎲</div>
                    <div class="dice" id="dice-2">🎲</div>
                    <div class="dice" id="dice-3">🎲</div>
                    <div class="dice" id="dice-4">🎲</div>
                    <div class="dice" id="dice-5">🎲</div>
                </div>
                
                <div class="step-actions">
                    <button class="action-button" id="roll-dice-btn">Roll the Cosmic Dice ✨</button>
                    <button class="action-button secondary" id="lock-name-btn" style="display: none;">Lock This Name 🔒</button>
                </div>
                
                <div class="roll-counter" id="roll-counter">Rolls: <span id="roll-count">0</span> / 5</div>
            </div>
            
            <!-- Step 2: Tribe Sorting -->
            <div class="initiation-step" id="step-tribe-sorting" style="display: none;">
                <div class="step-header">
                    <div class="step-avatar">
                        <!-- Emoji Narrator for this step -->
                        <div class="narrator-container" id="narrator-container-2">
                            <!-- Narrator elements will be created by JavaScript -->
                        </div>
                    </div>
                    <h2 class="step-title">The Cosmic Shell Game</h2>
                    <p class="step-subtitle">The universe shuffles fate... choose your destiny</p>
                </div>
                
                <div class="shell-game-container" id="shell-game-container">
                    <div class="cosmic-shells">
                        <div class="shell-cup" id="cup-1" data-tribe="dank_dynasty">
                            <div class="cup-lid">🌟</div>
                            <div class="cup-base">
                                <div class="hidden-tribe">🔥</div>
                            </div>
                        </div>
                        <div class="shell-cup" id="cup-2" data-tribe="blazed_ones">
                            <div class="cup-lid">🌟</div>
                            <div class="cup-base">
                                <div class="hidden-tribe">😇</div>
                            </div>
                        </div>
                        <div class="shell-cup" id="cup-3" data-tribe="holy_smokes">
                            <div class="cup-lid">🌟</div>
                            <div class="cup-base">
                                <div class="hidden-tribe">💨</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="shuffle-stage" id="shuffle-stage">
                        <div class="cosmic-energy"></div>
                        <div class="shuffle-text">The cosmos is shuffling your fate...</div>
                    </div>
                    
                    <div class="choice-prompt" id="choice-prompt" style="display: none;">
                        <div class="prompt-text">✨ Choose your cosmic vessel ✨</div>
                        <div class="prompt-subtitle">Trust your intuition...</div>
                    </div>
                </div>
                
                <div class="tribe-revelation" id="tribe-revelation" style="display: none;">
                    <div class="revelation-container">
                        <div class="cosmic-burst"></div>
                        <div class="tribe-card-epic">
                            <div class="tribe-emoji-massive" id="tribe-emoji-result"></div>
                            <div class="tribe-name-epic" id="tribe-name-result"></div>
                            <div class="tribe-tagline-epic" id="tribe-tagline-result"></div>
                        </div>
                    </div>
                </div>
                
                <div class="step-actions">
                    <button class="action-button" id="start-shuffle-btn">Begin the Cosmic Shuffle 🪄</button>
                    <button class="action-button secondary" id="accept-tribe-btn" style="display: none;">Accept My Tribe ✨</button>
                </div>
            </div>
            
            <!-- Step 3: DOB Ritual -->
            <div class="initiation-step" id="step-dob-ritual" style="display: none;">
                <div class="step-header">
                    <div class="step-avatar">
                        <!-- Emoji Narrator for this step -->
                        <div class="narrator-container" id="narrator-container-3">
                            <!-- Narrator elements will be created by JavaScript -->
                        </div>
                    </div>
                    <h2 class="step-title">Cosmic Alignment</h2>
                    <p class="step-subtitle">The stars need to know when you arrived</p>
                </div>
                
                <div class="dob-form-container">
                    <div class="zodiac-background" id="zodiac-background"></div>
                    
                    <div class="dob-inputs">
                        <div class="input-group">
                            <label for="birth-month">Birth Month</label>
                            <select id="birth-month" class="cosmic-select">
                                <option value="">Select Month</option>
                                <option value="01">January</option>
                                <option value="02">February</option>
                                <option value="03">March</option>
                                <option value="04">April</option>
                                <option value="05">May</option>
                                <option value="06">June</option>
                                <option value="07">July</option>
                                <option value="08">August</option>
                                <option value="09">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                        
                        <div class="input-group">
                            <label for="birth-year">Birth Year</label>
                            <select id="birth-year" class="cosmic-select">
                                <option value="">Select Year</option>
                                <?php for($year = 2006; $year >= 1950; $year--): ?>
                                    <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="zodiac-message" id="zodiac-message">
                        The cosmos awaits your alignment...
                    </div>
                </div>
                
                <div class="step-actions">
                    <button class="action-button" id="align-cosmos-btn">Align with the Cosmos 🌌</button>
                </div>
            </div>
            
            <!-- Step 4: Final Blessing -->
            <div class="initiation-step" id="step-final-blessing" style="display: none;">
                <div class="step-header">
                    <div class="step-avatar">
                        <!-- Emoji Narrator for this step -->
                        <div class="narrator-container" id="narrator-container-4">
                            <!-- Narrator elements will be created by JavaScript -->
                        </div>
                    </div>
                    <h2 class="step-title">The Final Blessing</h2>
                    <p class="step-subtitle">Your transformation is complete</p>
                </div>
                
                <div class="blessing-content">
                    <div class="angel-summary" id="angel-summary">
                        <div class="summary-item">
                            <span class="summary-label">Angel Name:</span>
                            <span class="summary-value" id="summary-name">-</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Sacred Tribe:</span>
                            <span class="summary-value" id="summary-tribe">-</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Cosmic Alignment:</span>
                            <span class="summary-value" id="summary-alignment">-</span>
                        </div>
                    </div>
                    
                    <div class="blessing-reward" id="blessing-reward">
                        <div class="reward-icon">💰</div>
                        <div class="reward-text">£5 Angel Credit</div>
                        <div class="reward-subtext">Blessed upon your wallet</div>
                    </div>
                </div>
                
                <div class="step-actions">
                    <button class="action-button" id="complete-initiation-btn">Complete My Initiation 🚀</button>
                </div>
            </div>
            
            <!-- Completion Celebration -->
            <div class="completion-celebration" id="completion-celebration" style="display: none;">
                <div class="celebration-emoji">🎉✨🦋</div>
                <h2 class="celebration-title">Welcome, Angel!</h2>
                <p class="celebration-message">
                    Your cosmic transformation is complete. You are now officially blessed and ready to explore the Green Angel universe.
                </p>
                <button class="action-button" id="enter-dashboard-btn">Enter Your Angel Realm 🌈</button>
            </div>
            
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div class="cosmic-loading-overlay" id="cosmic-loading-overlay" style="display: none;">
        <div class="loading-spinner">
            <div class="spinner-emoji">✨</div>
            <div class="loading-text">Channeling cosmic energy...</div>
        </div>
    </div>
    
    
</div>
<!-- End Angel Initiation Ceremony -->