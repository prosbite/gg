import { ref, onUnmounted } from 'vue'

export interface ImageEntry {
  id: string
  file: File
  label: string
  previewUrl: string
}

let idCounter = 0

export function useImageGallery() {
  const images = ref<ImageEntry[]>([])
  const fileInput = ref<HTMLInputElement | null>(null)
  const videoRef = ref<HTMLVideoElement | null>(null)
  const showCamera = ref(false)
  let stream: MediaStream | null = null

  const addFile = (file: File) => {
    const id = `img_${++idCounter}_${Date.now()}`
    const previewUrl = URL.createObjectURL(file)
    images.value.push({ id, file, label: '', previewUrl })
  }

  const onFileSelect = (e: Event) => {
    const input = e.target as HTMLInputElement
    const files = input.files
    if (files) {
      for (let i = 0; i < files.length; i++) {
        addFile(files[i])
      }
    }
    input.value = ''
  }

  const startCamera = async () => {
    try {
      stream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'environment' }
      })
      if (videoRef.value) videoRef.value.srcObject = stream
      showCamera.value = true
    } catch {
      alert('Camera access denied or not available')
    }
  }

  const capturePhoto = () => {
    if (!videoRef.value) return
    const canvas = document.createElement('canvas')
    canvas.width = videoRef.value.videoWidth
    canvas.height = videoRef.value.videoHeight
    canvas.getContext('2d')!.drawImage(videoRef.value, 0, 0)
    canvas.toBlob((blob) => {
      if (!blob) return
      const file = new File([blob], `photo_${Date.now()}.jpg`, { type: 'image/jpeg' })
      addFile(file)
    }, 'image/jpeg')
  }

  const stopCamera = () => {
    stream?.getTracks().forEach(track => track.stop())
    stream = null
    showCamera.value = false
  }

  const removeImage = (id: string) => {
    const idx = images.value.findIndex(img => img.id === id)
    if (idx !== -1) {
      URL.revokeObjectURL(images.value[idx].previewUrl)
      images.value.splice(idx, 1)
    }
  }

  const updateLabel = (id: string, label: string) => {
    const img = images.value.find(img => img.id === id)
    if (img) img.label = label
  }

  const clearAll = () => {
    images.value.forEach(img => URL.revokeObjectURL(img.previewUrl))
    images.value = []
  }

  onUnmounted(() => {
    clearAll()
    stopCamera()
  })

  return {
    images,
    fileInput,
    videoRef,
    showCamera,
    addFile,
    onFileSelect,
    startCamera,
    capturePhoto,
    stopCamera,
    removeImage,
    updateLabel,
    clearAll,
  }
}
