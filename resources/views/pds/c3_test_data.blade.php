{{-- Test Data Populator for C3 --}}
<script>
    (function() {
        // Wait for main script to initialize
        function waitAndPopulate() {
            // Check if main script has initialized by looking for the add functions
            if (typeof window.addLearningEntry !== 'function' || typeof window.addVoluntaryEntry !== 'function') {
                console.log('Waiting for main script to initialize...');
                setTimeout(waitAndPopulate, 300);
                return;
            }

            // Check if already populated
            const existingVoluntary = document.querySelectorAll('#voluntary-container .entry-card').length;
            const existingLearning = document.querySelectorAll('#learning-container .entry-card').length;
            
            if (existingVoluntary > 0 || existingLearning > 0) {
                console.log('Data already exists, skipping population');
                return;
            }

            console.log('Populating test data...');

            // Add 10 Voluntary Work entries
            for (let i = 1; i <= 10; i++) {
                window.addVoluntaryEntry({
                    voluntary_org: `Test Organization ${i}, Address ${i}`,
                    voluntary_from: `202${(i % 3) + 1}-01-01`,
                    voluntary_to: `202${(i % 3) + 1}-12-31`,
                    voluntary_hours: (i * 10) + 20,
                    voluntary_position: `Volunteer Position ${i}`
                });
            }

            // Add 20 Learning and Development entries
            const trainingTypes = ['Managerial', 'Supervisory', 'Technical', 'Others'];
            const trainingTitles = [
                'Leadership and Management Training',
                'Project Management Fundamentals',
                'Public Service Excellence',
                'Crisis Management Workshop',
                'Digital Transformation Training',
                'Data Privacy and Security',
                'Effective Communication Skills',
                'Team Building and Collaboration',
                'Customer Service Excellence',
                'Strategic Planning Workshop',
                'Budget Management Training',
                'Human Resource Development',
                'Policy Implementation Workshop',
                'Ethics and Governance Training',
                'Disaster Preparedness Training',
                'Environmental Management',
                'Community Development Training',
                'Records Management System',
                'Public Financial Management',
                'Performance Management Workshop'
            ];

            for (let i = 1; i <= 20; i++) {
                window.addLearningEntry({
                    learning_title: trainingTitles[i - 1] || `Training Program ${i}`,
                    learning_type: trainingTypes[i % 4],
                    learning_from: `202${(i % 4) + 1}-01-01`,
                    learning_to: `202${(i % 4) + 1}-12-31`,
                    learning_hours: (i * 5) + 10,
                    learning_conducted: `Conducted by Agency ${i}`
                });
            }

            // Add Other Information - Skills (5 entries)
            const skills = [
                'Computer Programming',
                'Data Analysis',
                'Project Management',
                'Public Speaking',
                'Research and Documentation'
            ];

            // Clear existing empty skill inputs first
            const skillsContainer = document.getElementById('skills-container');
            if (skillsContainer) {
                skillsContainer.innerHTML = '';
                skills.forEach(skill => {
                    window.addField('skills-container', 'skills[]', 'Enter special skill or hobby', skill);
                });
            }

            // Add Other Information - Distinctions (3 entries)
            const distinctions = [
                'Employee of the Year 2023',
                'Outstanding Public Service Award',
                'Civic Achievement Recognition'
            ];
            const distinctionsContainer = document.getElementById('distinctions-container');
            if (distinctionsContainer) {
                distinctionsContainer.innerHTML = '';
                distinctions.forEach(distinction => {
                    window.addField('distinctions-container', 'distinctions[]', 'Enter non-academic distinction or recognition', distinction);
                });
            }

            // Add Other Information - Organizations (4 entries)
            const organizations = [
                'Philippine Red Cross - Local Chapter',
                'Rotary Club of Metro City',
                'Local Government League',
                'Public Service Association'
            ];
            const orgContainer = document.getElementById('organizations-container');
            if (orgContainer) {
                orgContainer.innerHTML = '';
                organizations.forEach(org => {
                    window.addField('organizations-container', 'organizations[]', 'Enter organization name', org);
                });
            }

            // Update entry counts
            if (typeof window.updateEntryCount === 'function') {
                window.updateEntryCount();
            }
            if (typeof window.checkEmptyStates === 'function') {
                window.checkEmptyStates();
            }

            console.log('Test data populated successfully!');
            alert('Test data populated: 10 Voluntary Work, 20 L&D entries, and Other Information filled!');
        }

        // Start waiting when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(waitAndPopulate, 800);
            });
        } else {
            setTimeout(waitAndPopulate, 800);
        }
    })();
</script>
