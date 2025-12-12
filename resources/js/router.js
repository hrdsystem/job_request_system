import { createRouter, createWebHistory } from 'vue-router'

const JobRequestPage = () => import('../components/pages/JobRequestPage.vue');
const JobRequiredPage = () => import('../components/pages/job_request_settings/JobRequiredPage.vue');
const JobEmailRecipientPage = () => import('../components/pages/job_request_settings/JobRequestEmailRecipientsPage.vue')
const Page404 = () => import('../components/includes/Page404.vue');

// import Home from './pages/Home.vue';
// import Company from '../pages/Company.vue';

const router = createRouter({
    history: createWebHistory('/job_request/'),
    base: '/job_request/',
    routes: [
        // {
        //     path: '/',
        //     component: Page404,
        //     name: '404',
        // },
        {
            path: '/',
            component: JobRequestPage,
            name: 'job_request',
        },
        {
            path: '/job_request_settings/job_required',
            component: JobRequiredPage,
            name: 'job_required',
        },
        {
            path: '/job_request_settings/email_recipients',
            component: JobEmailRecipientPage,
            name: 'job_email_recipients'
        }
    ]
});

export default router;
