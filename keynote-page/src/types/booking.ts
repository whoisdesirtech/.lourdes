export interface BookingInquiry {
  id?: string
  name: string
  email: string
  organization?: string
  eventName?: string
  message: string
  createdAt: Date
  status: 'new' | 'reviewed' | 'replied'
}

export type SubmitStatus = 'idle' | 'submitting' | 'success' | 'error'
