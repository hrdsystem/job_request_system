import { createRouter, createWebHistory } from 'vue-router'

const JobRequestPage = () => import('../components/pages/JobRequestPage.vue');
const JobRequiredPage = () => import('../components/pages/job_request_settings/JobRequiredPage.vue');
const Page404 = () => import('../components/includes/Page404.vue');

// import Home from './pages/Home.vue';
// import Company from '../pages/Company.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '/',
            component: Page404,
            name: '404',
        },
        {
            path: '/job_request',
            component: JobRequestPage,
            name: 'job_request',
        },
        {
            path: '/job_request_settings/job_required',
            component: JobRequiredPage,
            name: 'job_required',
        },
    ]
});

export default router;
