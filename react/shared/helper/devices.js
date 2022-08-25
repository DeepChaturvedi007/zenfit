const size = {
    mobileScreen: '1024px',
    laptopScreen: '1024px'
}

export const DEVICE = {
    mobile: `@media (max-width: ${size.mobileScreen})`,
    desktop: `@media (min-width: ${size.mobileScreen})`
};

export const IS_MOBILE = /Mobi|Android/i.test(navigator.userAgent)
