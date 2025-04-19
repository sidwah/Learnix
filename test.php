<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learnix Badge System Test</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        h1, h2 {
            color: #333;
        }
        
        .badge-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin: 20px 0;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        
        .controls {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #eef2f7;
            border-radius: 8px;
        }
        
        .btn {
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px 15px;
            cursor: pointer;
            margin-right: 10px;
        }
        
        .btn:hover {
            background-color: #357abd;
        }
        
        select, input {
            padding: 8px;
            margin-right: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        
        label {
            margin-right: 5px;
        }
        
        .settings-row {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Learnix Badge System Test</h1>
        
        <div class="controls">
            <h2>Badge Generator Test</h2>
            <div class="settings-row">
                <label for="achievement-type">Achievement Type:</label>
                <select id="achievement-type">
                    <option value="section_completion">Section Completion</option>
                    <option value="course_completion">Course Completion</option>
                    <option value="streak">Learning Streak</option>
                    <option value="perfect_score">Perfect Score</option>
                    <option value="speed_learner">Speed Learner</option>
                    <option value="top_performer">Top Performer</option>
                </select>
                
                <label for="tier-level">Tier Level:</label>
                <select id="tier-level">
                    <option value="basic">Basic</option>
                    <option value="gold">Gold</option>
                    <option value="platinum">Platinum</option>
                </select>
            </div>
            
            <div class="settings-row">
                <label for="course-name">Course Name:</label>
                <input type="text" id="course-name" placeholder="Course name for details">
                
                <label for="section-name">Section Name:</label>
                <input type="text" id="section-name" placeholder="Section name for details">
            </div>
            
            <div class="settings-row">
                <label for="streak-days">Streak Days:</label>
                <input type="number" id="streak-days" value="7" min="1" max="365">
                
                <label for="quiz-name">Quiz Name:</label>
                <input type="text" id="quiz-name" placeholder="Quiz name for perfect score">
            </div>
            
            <div class="settings-row">
                <button id="generate-badge" class="btn">Generate Single Badge</button>
                <button id="generate-all" class="btn">Generate All Combinations</button>
                <button id="show-achievement" class="btn">Show Achievement Animation</button>
                <button id="clear-badges" class="btn">Clear All</button>
            </div>
        </div>
        
        <h2>Generated Badges</h2>
        <div id="badge-container" class="badge-container">
            <!-- Badges will be displayed here -->
            <div class="empty-state">No badges generated yet. Use the controls above to create badges.</div>
        </div>
    </div>
    
    <!-- Load badge scripts -->
    <script src="assets/js/badges/badge-generator.js"></script>
    <script src="assets/js/badges/badge-display.js"></script>
    
    <script>
        // Initialize the badge generator and display
        const generator = new BadgeGenerator({
            basePath: 'assets/img/badges/',
            tierPath: 'assets/img/badges/tiers/',
            iconPath: 'assets/img/badges/icons/'
        });
        
        const display = new BadgeDisplay({
            generator: generator,
            containerSelector: '#badge-container',
            animationEnabled: true,
            tooltipsEnabled: true
        });
        
        // Get DOM elements
        const badgeContainer = document.getElementById('badge-container');
        const achievementTypeSelect = document.getElementById('achievement-type');
        const tierLevelSelect = document.getElementById('tier-level');
        const courseNameInput = document.getElementById('course-name');
        const sectionNameInput = document.getElementById('section-name');
        const streakDaysInput = document.getElementById('streak-days');
        const quizNameInput = document.getElementById('quiz-name');
        
        // Generate button
        document.getElementById('generate-badge').addEventListener('click', async () => {
            const achievementType = achievementTypeSelect.value;
            const tierLevel = tierLevelSelect.value;
            
            // Prepare user data based on achievement type
            const userData = {
                courseName: courseNameInput.value,
                sectionName: sectionNameInput.value,
                streakDays: parseInt(streakDaysInput.value) || 7,
                quizName: quizNameInput.value,
                dateEarned: new Date()
            };
            
            // Generate badge SVG
            try {
                const svgString = await generator.generateBadge(achievementType, tierLevel, userData);
                
                // Display badge
                display.displayUserBadges(badgeContainer, [{
                    achievementType,
                    tierLevel,
                    userData,
                    svgContent: svgString,
                    title: generator.getAchievementTitle(achievementType),
                    description: generator.formatAchievementDetails(achievementType, userData),
                    dateEarned: new Date(),
                    shareEnabled: true
                }], { clear: false });
                
                // Remove empty state if present
                const emptyState = badgeContainer.querySelector('.empty-state');
                if (emptyState) {
                    emptyState.remove();
                }
            } catch (error) {
                console.error('Error generating badge:', error);
                alert('Error generating badge: ' + error.message);
            }
        });
        
        // Generate all combinations button
        document.getElementById('generate-all').addEventListener('click', async () => {
            // Clear existing badges
            badgeContainer.innerHTML = '';
            
            const achievementTypes = [
                'section_completion',
                'course_completion',
                'streak',
                'perfect_score',
                'speed_learner',
                'top_performer'
            ];
            
            const tierLevels = ['basic', 'gold', 'platinum'];
            
            // Generate badges with sample data
            const badges = [];
            
            for (const type of achievementTypes) {
                for (const tier of tierLevels) {
                    // Prepare user data based on achievement type
                    const userData = {
                        courseName: 'Sample Course',
                        sectionName: 'Introduction to Learning',
                        streakDays: 7,
                        quizName: 'Final Assessment',
                        dateEarned: new Date()
                    };
                    
                    try {
                        const svgString = await generator.generateBadge(type, tier, userData);
                        
                        badges.push({
                            achievementType: type,
                            tierLevel: tier,
                            userData,
                            svgContent: svgString,
                            title: generator.getAchievementTitle(type) + ` (${tier})`,
                            description: generator.formatAchievementDetails(type, userData),
                            dateEarned: new Date(),
                            shareEnabled: true
                        });
                    } catch (error) {
                        console.error(`Error generating badge for ${type} - ${tier}:`, error);
                    }
                }
            }
            
            // Display all badges
            display.displayUserBadges(badgeContainer, badges, { clear: true });
        });
        
        // Show achievement animation button
        document.getElementById('show-achievement').addEventListener('click', async () => {
            const achievementType = achievementTypeSelect.value;
            const tierLevel = tierLevelSelect.value;
            
            // Prepare user data based on achievement type
            const userData = {
                courseName: courseNameInput.value,
                sectionName: sectionNameInput.value,
                streakDays: parseInt(streakDaysInput.value) || 7,
                quizName: quizNameInput.value,
                dateEarned: new Date()
            };
            
            // Generate badge SVG
            try {
                const svgString = await generator.generateBadge(achievementType, tierLevel, userData);
                
                // Show achievement animation
                display.displayNewBadgeAchievement({
                    achievementType,
                    tierLevel,
                    userData,
                    svgContent: svgString,
                    title: generator.getAchievementTitle(achievementType),
                    description: generator.formatAchievementDetails(achievementType, userData),
                    dateEarned: new Date()
                });
            } catch (error) {
                console.error('Error showing achievement:', error);
                alert('Error showing achievement: ' + error.message);
            }
        });
        
        // Clear badges button
        document.getElementById('clear-badges').addEventListener('click', () => {
            badgeContainer.innerHTML = '<div class="empty-state">No badges generated yet. Use the controls above to create badges.</div>';
        });
    </script>
</body>
</html>