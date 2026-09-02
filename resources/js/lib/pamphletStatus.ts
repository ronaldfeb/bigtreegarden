export function isPamphletPaid(status: string): boolean {
    return status === 'paid' || status === 'published';
}

export function isPamphletUnpaid(status: string): boolean {
    return status === 'draft' || status === 'pending_payment';
}
