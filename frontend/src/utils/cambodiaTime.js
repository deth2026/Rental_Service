export const CAMBODIA_TIMEZONE = 'Asia/Phnom_Penh'

const keyFormatter = new Intl.DateTimeFormat('en-CA', {
  timeZone: CAMBODIA_TIMEZONE,
  year: 'numeric',
  month: '2-digit',
  day: '2-digit',
})

const yearFormatter = new Intl.DateTimeFormat('en-CA', {
  timeZone: CAMBODIA_TIMEZONE,
  year: 'numeric',
})

const weekdayFormatter = new Intl.DateTimeFormat('en-US', {
  timeZone: CAMBODIA_TIMEZONE,
  weekday: 'short',
})

const dateTimeFormatter = new Intl.DateTimeFormat('en-GB', {
  timeZone: CAMBODIA_TIMEZONE,
  year: 'numeric',
  month: 'short',
  day: '2-digit',
  hour: '2-digit',
  minute: '2-digit',
  second: '2-digit',
  hour12: false,
})

export const cambodiaDateKey = (value = new Date()) => keyFormatter.format(new Date(value))

export const cambodiaYear = (value = new Date()) => Number(yearFormatter.format(new Date(value)))

export const cambodiaWeekdayLabel = (value = new Date()) =>
  String(weekdayFormatter.format(new Date(value)) || '').toUpperCase()

export const cambodiaDateTimeLabel = (value = new Date()) => dateTimeFormatter.format(new Date(value))

