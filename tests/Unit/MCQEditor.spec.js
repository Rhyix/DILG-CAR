import { mount } from '@vue/test-utils';
import MCQEditor from '../../resources/views/admin/exam_edit_vue.blade.php';

describe('MCQEditor', () => {
    it('renders the component', () => {
        const wrapper = mount(MCQEditor, {
            props: {
                question: {
                    id: 1,
                    text: 'What is the capital of France?',
                    type: 'MCQ',
                    answer: 'Paris',
                    choices: [
                        { letter: 'A', text: 'London' },
                        { letter: 'B', text: 'Paris' },
                        { letter: 'C', text: 'Berlin' },
                        { letter: 'D', text: 'Madrid' },
                    ],
                    is_required: true,
                    errors: {},
                },
            },
        });
        expect(wrapper.exists()).toBe(true);
    });
});
