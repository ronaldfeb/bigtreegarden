export type MarketingHeroBanner = {
    src: string;
    alt: string;
};

const encodeAssetPath = (path: string): string =>
    `/assets/${path.split('/').map((segment) => encodeURIComponent(segment)).join('/')}`;

const bannerPaths = [
    'banners/Kids [Potraits]/Kids (0-12) Background-Boys (Potrait).webp',
    'banners/Kids [Potraits]/Kids (0-12) Background-Girls (Potrait).webp',
    'banners/Teens [Potrait]/Teens (13-18) Background-Males (Potrait).webp',
    'banners/Teens [Potrait]/Teens (13-18) Background-Females (Potrait).webp',
    'banners/Adults [Potraits]/Adults (35–54) Background (Potrait).webp',
    'banners/Adults [Potraits]/Adults (35–54) Background-Males (Potrait).webp',
    'banners/Kids [Potraits]/Kids (0-12) Background-Boys (Potrait) (2).webp',
] as const;

export const marketingHeroBanners: MarketingHeroBanner[] = bannerPaths.map((path, index) => ({
    src: encodeAssetPath(path),
    alt: `Memorial portrait background ${index + 1}`,
}));

/** Adults portrait banner used in marketing pamphlet sample preview */
export const marketingSampleBanner: MarketingHeroBanner = {
    src: encodeAssetPath('banners/Adults [Potraits]/Adults (35–54) Background (Potrait).webp'),
    alt: 'Sample memorial pamphlet background',
};
