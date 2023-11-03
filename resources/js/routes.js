import Dashboard from './components/Dashboard.vue'
import Appointments from './pages/appointments/ListAppointments.vue'
import Users from './pages/users/ListUsers.vue'
import Settings from './pages/settings/UpdateSettings.vue'
import Profile from './pages/profile/Profile.vue'

export default [
    {
        path:'/admin/dashboard',
        name: 'admin.dashboard',
        component: Dashboard
    },
    {
        path:'/admin/appointments',
        name: 'admin.appointments',
        component: Appointments
    },
    {
        path:'/admin/users',
        name: 'admin.users',
        component: Users
    },
    {
        path:'/admin/settings',
        name: 'admin.settings',
        component: Settings
    },
    {
        path:'/admin/profile',
        name: 'admin.profile',
        component: Profile
    }
]
