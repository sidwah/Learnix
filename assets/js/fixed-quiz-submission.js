// This script replaces the existing submit functionality with a simpler, more reliable approach
document.addEventListener('DOMContentLoaded', function() {
    console.log('Quiz submission fix loaded');
    
    // Wait a short time to ensure all other scripts have loaded
    setTimeout(function() {
        // Get the submit button
        const submitBtn = document.getElementById('submitQuizBtn');
        if (!submitBtn) {
            console.error('Submit button not found!');
            return;
        }
        
        // Remove all existing click handlers
        const newSubmitBtn = submitBtn.cloneNode(true);
        submitBtn.parentNode.replaceChild(newSubmitBtn, submitBtn);
        
        // Add our new submit handler
        newSubmitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('New submit handler activated');
            
            // Get the quiz slide container and attempt ID
            const quizSlideContainer = document.getElementById('quizSlideContainer');
            const attemptId = quizSlideContainer.getAttribute('data-attempt-id');
            
            if (!attemptId) {
                alert("Error: Could not find quiz attempt information. Please reload the page and try again.");
                console.error("No attempt ID found");
                return;
            }
            
            // Confirmation dialog
            if (confirm("Are you sure you want to submit your quiz? You won't be able to change your answers after submission.")) {
                console.log(`Submitting quiz with attempt ID: ${attemptId}`);
                
                // Show loading overlay
                const loaderDiv = document.createElement('div');
                loaderDiv.style.position = 'fixed';
                loaderDiv.style.top = '0';
                loaderDiv.style.left = '0';
                loaderDiv.style.width = '100%';
                loaderDiv.style.height = '100%';
                loaderDiv.style.backgroundColor = 'rgba(0,0,0,0.5)';
                loaderDiv.style.display = 'flex';
                loaderDiv.style.justifyContent = 'center';
                loaderDiv.style.alignItems = 'center';
                loaderDiv.style.zIndex = '10000';
                loaderDiv.innerHTML = '<div style="background: white; padding: 20px; border-radius: 5px;"><div class="spinner-border text-primary me-3" role="status"></div><span>Submitting quiz...</span></div>';
                document.body.appendChild(loaderDiv);
                
                // First save all responses before submitting
                saveAllResponses(attemptId, function() {
                    // Then submit the quiz
                    submitQuiz(attemptId, loaderDiv);
                });
            }
        });
        
        console.log('New submit handler attached');
    }, 500);
    
    // Function to save all responses
    function saveAllResponses(attemptId, callback) {
        const quizContent = document.getElementById('quizContent');
        const forms = quizContent.querySelectorAll('.quiz-question form');
        console.log(`Found ${forms.length} forms to save`);
        
        if (forms.length === 0) {
            console.log("No forms to save, proceeding to submission");
            if (callback) callback();
            return;
        }
        
        let savedCount = 0;
        const totalForms = forms.length;
        
        // Save each form
        forms.forEach(form => {
            const questionId = form.getAttribute('data-question-id');
            if (!questionId) {
                console.warn("Form without question ID, skipping");
                savedCount++;
                if (savedCount === totalForms && callback) {
                    callback();
                }
                return;
            }
            
            // Create form data
            const formData = new FormData(form);
            formData.append('attempt_id', attemptId);
            formData.append('question_id', questionId);
            
            // Send AJAX request
            console.log(`Saving response for question ${questionId}`);
            fetch('../ajax/students/save-quiz-response.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    console.log(`Saved question ${questionId}`);
                } catch (e) {
                    console.error(`Error parsing response for question ${questionId}:`, text, e);
                }
                
                // Increment counter regardless of success/failure
                savedCount++;
                console.log(`Saved ${savedCount}/${totalForms} questions`);
                
                // If all forms have been processed, call the callback
                if (savedCount === totalForms && callback) {
                    console.log("All responses processed, proceeding to submission");
                    callback();
                }
            })
            .catch(error => {
                console.error(`Error saving question ${questionId}:`, error);
                savedCount++;
                if (savedCount === totalForms && callback) {
                    callback();
                }
            });
        });
    }
    
    // Function to submit the quiz
    function submitQuiz(attemptId, loaderDiv) {
        console.log(`Submitting quiz with attempt ID: ${attemptId}`);
        
        // Get URL parameters for redirect
        const urlParams = new URLSearchParams(window.location.search);
        const courseId = urlParams.get('course_id');
        const topicId = urlParams.get('topic');
        
        // Construct the form data
        const formData = new FormData();
        formData.append('attempt_id', attemptId);
        
        // Use fetch API with proper error handling
        fetch('../ajax/students/submit-quiz.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Server responded with status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            console.log("Server response:", text);
            try {
                const data = JSON.parse(text);
                
                if (data.success) {
                    console.log("Quiz submitted successfully!");
                    // Redirect to results page
                    window.location.href = `?course_id=${courseId}&topic=${topicId}&attempt_id=${attemptId}&view=results`;
                } else {
                    // Show error message
                    throw new Error(data.message || "Unknown error submitting quiz");
                }
            } catch (e) {
                console.error("Error processing server response:", e);
                alert(`Error: ${e.message}. Please try again or contact support.`);
                if (loaderDiv) loaderDiv.remove();
            }
        })
        .catch(error => {
            console.error("Error submitting quiz:", error);
            alert(`Error submitting quiz: ${error.message}. Please try again.`);
            if (loaderDiv) loaderDiv.remove();
        });
    }
});