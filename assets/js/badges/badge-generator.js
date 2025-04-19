/**
 * Learnix Badge Generator
 * 
 * This module handles the dynamic generation of achievement badges by combining
 * tier templates with achievement icons and text overlays.
 */

class BadgeGenerator {
    /**
     * Initialize the badge generator
     * @param {Object} options - Configuration options
     */
    constructor(options = {}) {
        this.basePath = options.basePath || '/assets/img/badges/';
        this.tierPath = options.tierPath || this.basePath + 'tiers/';
        this.iconPath = options.iconPath || this.basePath + 'icons/';
        
        // Map of achievement types to their display names
        this.achievementTitles = {
            'section_completion': 'Section Completed',
            'course_completion': 'Course Completed',
            'streak': 'Learning Streak',
            'perfect_score': 'Perfect Score',
            'speed_learner': 'Speed Learner',
            'top_performer': 'Top Performer'
        };
        
        // Cache for loaded SVGs to prevent repeated fetches
        this.svgCache = {};
    }
    
    /**
     * Generates a complete badge SVG
     * @param {string} achievementType - Type of achievement (section_completion, etc.)
     * @param {string} tierLevel - Tier level (basic, gold, platinum)
     * @param {Object} userData - User-specific data for the badge
     * @returns {Promise<string>} - The complete SVG as a string
     */
    async generateBadge(achievementType, tierLevel, userData = {}) {
        try {
            // 1. Load the base tier template
            const tierTemplate = await this.loadTierTemplate(tierLevel);
            
            // 2. Load the achievement icon
            const achievementIcon = await this.loadAchievementIcon(achievementType);
            
            // 3. Prepare text elements
            const textElements = {
                title: this.getAchievementTitle(achievementType),
                details: this.formatAchievementDetails(achievementType, userData),
                date: this.formatDate(userData.dateEarned || new Date())
            };
            
            // 4. Combine elements into final SVG
            const finalBadge = this.assembleBadgeSVG(
                tierTemplate,
                achievementIcon,
                textElements
            );
            
            // 5. Return the completed SVG
            return finalBadge;
        } catch (error) {
            console.error('Error generating badge:', error);
            throw new Error('Failed to generate badge: ' + error.message);
        }
    }
    
    /**
     * Loads an SVG template from the server or cache
     * @param {string} path - Path to the SVG file
     * @returns {Promise<Document>} - The SVG as an XML document
     */
    async loadSVG(path) {
        // Check cache first
        if (this.svgCache[path]) {
            return this.svgCache[path].cloneNode(true);
        }
        
        try {
            const response = await fetch(path);
            if (!response.ok) {
                throw new Error(`Failed to load SVG from ${path}: ${response.status} ${response.statusText}`);
            }
            
            const svgText = await response.text();
            const parser = new DOMParser();
            const svgDoc = parser.parseFromString(svgText, 'image/svg+xml');
            
            // Check for parsing errors
            const parserError = svgDoc.querySelector('parsererror');
            if (parserError) {
                throw new Error('SVG parsing error: ' + parserError.textContent);
            }
            
            // Store in cache
            this.svgCache[path] = svgDoc.documentElement;
            
            return svgDoc.documentElement.cloneNode(true);
        } catch (error) {
            console.error(`Error loading SVG from ${path}:`, error);
            throw error;
        }
    }
    
    /**
     * Loads a tier template
     * @param {string} tierLevel - Tier level (basic, gold, platinum)
     * @returns {Promise<Document>} - The SVG template
     */
    async loadTierTemplate(tierLevel) {
        const validTiers = ['basic', 'gold', 'platinum'];
        const tier = validTiers.includes(tierLevel) ? tierLevel : 'basic';
        
        const path = `${this.tierPath}${tier}.svg`;
        return this.loadSVG(path);
    }
    
    /**
     * Loads an achievement icon
     * @param {string} achievementType - Type of achievement
     * @returns {Promise<Document>} - The SVG icon
     */
    async loadAchievementIcon(achievementType) {
        const validAchievements = Object.keys(this.achievementTitles);
        const achievement = validAchievements.includes(achievementType) 
            ? achievementType 
            : 'section_completion'; // Default
        
        const path = `${this.iconPath}${achievement}.svg`;
        return this.loadSVG(path);
    }
    
    /**
     * Gets the display title for an achievement type
     * @param {string} achievementType - Type of achievement
     * @returns {string} - Display title
     */
    getAchievementTitle(achievementType) {
        return this.achievementTitles[achievementType] || 'Achievement Unlocked';
    }
    
    /**
     * Formats achievement details based on user data
     * @param {string} achievementType - Type of achievement
     * @param {Object} userData - User-specific data
     * @returns {string} - Formatted details
     */
    formatAchievementDetails(achievementType, userData) {
        let details = '';
        
        switch (achievementType) {
            case 'section_completion':
                details = userData.sectionName 
                    ? `Completed: ${userData.sectionName}` 
                    : 'Section Completed';
                break;
                
            case 'course_completion':
                details = userData.courseName 
                    ? `Completed: ${userData.courseName}` 
                    : 'Course Completed';
                break;
                
            case 'streak':
                const days = userData.streakDays || 0;
                details = `${days} Day${days !== 1 ? 's' : ''} Learning Streak`;
                break;
                
            case 'perfect_score':
                details = userData.quizName 
                    ? `Perfect Score: ${userData.quizName}` 
                    : 'Perfect Quiz Score';
                break;
                
            case 'speed_learner':
                details = 'Completed faster than 90% of students';
                break;
                
            case 'top_performer':
                details = 'Top 10% of class performance';
                break;
                
            default:
                details = 'Achievement Unlocked';
        }
        
        return details;
    }
    
    /**
     * Formats a date for display on the badge
     * @param {Date} date - Date to format
     * @returns {string} - Formatted date
     */
    formatDate(date) {
        if (!(date instanceof Date)) {
            date = new Date(date);
        }
        
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }
    
    /**
     * Assembles all components into a final SVG
     * @param {Element} tierTemplate - The base tier SVG element
     * @param {Element} achievementIcon - The achievement icon SVG element
     * @param {Object} textElements - Text elements to include
     * @returns {string} - Complete SVG as a string
     */
    assembleBadgeSVG(tierTemplate, achievementIcon, textElements) {
        // Create a new SVG document to work with
        const svgNS = "http://www.w3.org/2000/svg";
        const newSvg = document.createElementNS(svgNS, "svg");
        
        // Copy attributes from the tier template
        Array.from(tierTemplate.attributes).forEach(attr => {
            newSvg.setAttribute(attr.name, attr.value);
        });
        
        // Ensure viewBox is set
        if (!newSvg.getAttribute('viewBox')) {
            newSvg.setAttribute('viewBox', '0 0 200 200');
        }
        
        // Copy the tier template content
        newSvg.innerHTML = tierTemplate.innerHTML;
        
        // Position and add the achievement icon
        const iconGroup = document.createElementNS(svgNS, "g");
        iconGroup.setAttribute('transform', 'translate(75, 65) scale(0.3)');
        iconGroup.innerHTML = achievementIcon.innerHTML;
        newSvg.appendChild(iconGroup);
        
        // Add text elements
        this.addTextElement(newSvg, textElements.title, { x: 100, y: 130, fontSize: '12px', fontWeight: 'bold' });
        this.addTextElement(newSvg, textElements.details, { x: 100, y: 150, fontSize: '10px' });
        this.addTextElement(newSvg, textElements.date, { x: 100, y: 170, fontSize: '8px', fontStyle: 'italic' });
        
        // Return the SVG as a string
        return new XMLSerializer().serializeToString(newSvg);
    }
    
    /**
     * Adds a text element to an SVG
     * @param {Element} svg - The SVG element to add text to
     * @param {string} text - The text content
     * @param {Object} options - Text options (position, style)
     */
    addTextElement(svg, text, options = {}) {
        const svgNS = "http://www.w3.org/2000/svg";
        const textElement = document.createElementNS(svgNS, "text");
        
        // Set attributes
        textElement.setAttribute('x', options.x || 0);
        textElement.setAttribute('y', options.y || 0);
        textElement.setAttribute('text-anchor', options.textAnchor || 'middle');
        textElement.setAttribute('dominant-baseline', options.dominantBaseline || 'middle');
        
        // Set style
        if (options.fontSize) textElement.style.fontSize = options.fontSize;
        if (options.fontFamily) textElement.style.fontFamily = options.fontFamily || 'Arial, sans-serif';
        if (options.fontWeight) textElement.style.fontWeight = options.fontWeight;
        if (options.fontStyle) textElement.style.fontStyle = options.fontStyle;
        if (options.fill) textElement.style.fill = options.fill || '#000000';
        
        // Set text content
        textElement.textContent = text;
        
        // Add to SVG
        svg.appendChild(textElement);
    }
    
    /**
     * Creates a data URL from an SVG string
     * @param {string} svgString - The SVG as a string
     * @returns {string} - Data URL
     */
    createDataUrl(svgString) {
        const encoded = encodeURIComponent(svgString);
        return `data:image/svg+xml;charset=utf-8,${encoded}`;
    }
    
    /**
     * Renders a badge to an image element
     * @param {string} svgString - The SVG as a string
     * @param {HTMLImageElement} imgElement - Image element to render to
     */
    renderToImage(svgString, imgElement) {
        if (!imgElement || !(imgElement instanceof HTMLImageElement)) {
            throw new Error('Invalid image element provided');
        }
        
        imgElement.src = this.createDataUrl(svgString);
    }
}

// Export the BadgeGenerator class
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BadgeGenerator;
} else {
    window.BadgeGenerator = BadgeGenerator;
}