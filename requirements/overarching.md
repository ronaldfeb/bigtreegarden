BigTreeGarden is a platform where users can create memorial pages of loved ones who have passed away. The core concept is the use of QR codes that link to memorial pages, memorial sites, and for use in utm tracking of promotions placed at service providers.

The core users

1. Visitors: users who are here to buy a memorial page and related features
2. Staff: users who are managing and administrating content on the platform
3. Service providers: users who manage their own profiles and integrations on the platform, kind of like a SAAS solution for funeral services providers where they can generate and offer memorial pages for their clients

The core features:
The core is centered around a person of interest. This person of interest then has many features such as associated memorial pages, memorial sites(physical), and vaults. 1 QR code should be linked to the person of interest - this will then link to their public page.

1. Purchase of a memorial page

- person of interest information is required
- A user can then create a customizable pamplet
- The the pamplet has a QR code that links to the memorial page of the person of interest
- must have an active day (memorial day, funeral day, unveiling, day)

2. Add memorial site

- User can add the location(s) of memorial sites for the person of interest.
- This must contain the GPS coordinates
- This is important: when a user scans the QR code we should determine whether they are within a 5 meter radius of the GPS Coordinates which then enables them to post comments on the memorial page (this is a paid feature and users are charged R5 per post)

3. Live commenting on a memorial page

- Should be enabled on the active day
- This page, when a user scans the QR code on the active day and is not at the GPS coordinates, should show an alert informing the user to "Remember to turn their phone on silent."
- Authenticated users can leave text and image type comments
- There should be a beautiful page that can open on a new tab with just the comments displayed chronologically. This page should refresh every second. No need for reverb

4. GPS based commenting

- Users should be able to scan a the Person of Interest QR code
- This should then open the memorial page
- We should determine if the user is within 5meter radius of the GPS coordinates of the memorial site
- Users are charged a fee to post a comment (we call these leaving flowers)

5. Person of interest vault (PAID)

- this should be their own little fortnox
- a user can create their vault
- in the vault they can add beneficiaries and at most 1 must be set as the executor
- for each of these beneficiaries we should request their email, contact number, physical address, an a unique code
- in this vault the user can then store media(images, videos, and pdf documents, mp3 voice notes)
- in this vault the user can post text posts for their beneficiaries (this could be for all beneficiaries or just a few)
- the vault is not available via the memorials, it is something that can be access by the beneficiaries using their codes.

Person of interest public page:

- must show the gallery
- must show the memorial sites
- must show the comments
- must be mobile friendly and good modern ux

Staff of the platform (SHADCN):
You should develop our own CMS for this section where staff can administer everything of the platform, leave out the vault for now.
Marketing staff should be able to generate new links for marketing ads, the link should have a utm code for tracking a specific location. So we should allow them to generate a QR code that contains the name of the "client" where they will post the physical ad. This in turn should feed an analytics page where they can view how many unique visits were received at that link.

Service providers:
For this iteration a service provider can manage their page details

Auxiliary

- We have policies like terms of service, privacy policy, and about us - this can be manages in one table
- Help Center for general public access
- Blogs for general public access
- Testimonials for general public access
