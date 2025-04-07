import { createPinia } from 'pinia'
import useAppStore from '@/Store/app'

const pinia = createPinia()

export { useAppStore }
export default pinia
