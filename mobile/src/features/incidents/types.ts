export const incidentCategories = ['Flood', 'Earthquake', 'Landslide', 'Fire'] as const;

export type IncidentCategory = (typeof incidentCategories)[number];

export type CreateIncidentRequest = {
  category: IncidentCategory;
  barangay: string;
  nearest_landmark: string;
  affected_population: number;
  latitude: number;
  longitude: number;
  impact_radius: number;
  notes: string | null;
};
