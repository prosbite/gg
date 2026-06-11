import { ref, onUnmounted } from 'vue'
import axios from 'axios'

export interface CustomerSearchResult {
  id: number
  first_name: string
  last_name: string
  contact_number: string | null
  address: string | null
  affiliation: string | null
  social_media_link: string | null
}

export function formatCustomerName(
  first: string,
  last: string,
  format: 'first-last' | 'last-first' = 'first-last'
): string {
  return format === 'last-first' ? `${last}, ${first}` : `${first} ${last}`
}

export function getCustomerInitials(first: string, last: string): string {
  return (first.charAt(0) + last.charAt(0)).toUpperCase()
}

export function useCustomerSearch() {
  const searchResults = ref<CustomerSearchResult[]>([])
  const showSearch = ref(false)
  let timeout: ReturnType<typeof setTimeout> | null = null

  onUnmounted(() => {
    if (timeout) clearTimeout(timeout)
  })

  const search = (q: string) => {
    if (timeout) clearTimeout(timeout)
    if (q.length < 2) {
      searchResults.value = []
      showSearch.value = false
      return
    }
    timeout = setTimeout(async () => {
      try {
        const { data } = await axios.get(route('customers.search'), { params: { q } })
        searchResults.value = data
        showSearch.value = data.length > 0
      } catch {
        searchResults.value = []
        showSearch.value = false
      }
    }, 350)
  }

  const clearResults = () => {
    if (timeout) clearTimeout(timeout)
    searchResults.value = []
    showSearch.value = false
  }

  return { searchResults, showSearch, search, clearResults }
}
