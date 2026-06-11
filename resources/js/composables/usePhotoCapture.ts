import { ref, onUnmounted } from 'vue'

export function blobToFile(blob: Blob, filename?: string): File {
  return new File([blob], filename ?? `photo_${Date.now()}.jpg`, { type: blob.type || 'image/jpeg' })
}

export function usePhotoCapture() {
  const previewUrl = ref<string | null>(null)
  const fileInput = ref<HTMLInputElement | null>(null)
  const videoRef = ref<HTMLVideoElement | null>(null)
  const showCamera = ref(false)
  let stream: MediaStream | null = null

  const onFileSelect = (e: Event, onFile: (file: File) => void) => {
    const file = (e.target as HTMLInputElement).files?.[0]
    if (!file) return
    onFile(file)
    previewUrl.value = URL.createObjectURL(file)
  }

  const startCamera = async () => {
    try {
      stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
      if (videoRef.value) videoRef.value.srcObject = stream
      showCamera.value = true
    } catch {
      alert('Camera access denied or not available')
    }
  }

  const capturePhoto = (onFile: (file: File) => void) => {
    if (!videoRef.value) return
    const canvas = document.createElement('canvas')
    canvas.width = videoRef.value.videoWidth
    canvas.height = videoRef.value.videoHeight
    canvas.getContext('2d')!.drawImage(videoRef.value, 0, 0)
    canvas.toBlob((blob) => {
      if (!blob) return
      const file = new File([blob], `photo_${Date.now()}.jpg`, { type: 'image/jpeg' })
      onFile(file)
      previewUrl.value = URL.createObjectURL(file)
      stopCamera()
    }, 'image/jpeg')
  }

  const stopCamera = () => {
    stream?.getTracks().forEach(track => track.stop())
    stream = null
    showCamera.value = false
  }

  const removePhoto = () => {
    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
    previewUrl.value = null
    if (fileInput.value) fileInput.value.value = ''
  }

  onUnmounted(() => {
    if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
    stopCamera()
  })

  return {
    previewUrl,
    fileInput,
    videoRef,
    showCamera,
    onFileSelect,
    startCamera,
    capturePhoto,
    stopCamera,
    removePhoto,
  }
}
