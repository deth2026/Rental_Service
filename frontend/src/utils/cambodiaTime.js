export const CAMBODIA_TIMEZONE = 'Asia/Phnom_Penh'

export function cambodiaDateTimeLabel(date = new Date()) {
  return date.toLocaleString('en-US', {
    timeZone: CAMBODIA_TIMEZONE,
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit',
    hour12: true,
  })
}

export function cambodiaYear(date = new Date()) {
  return date.toLocaleString('en-US', {
    timeZone: CAMBODIA_TIMEZONE,
    year: 'numeric',
  })
}
