export type VolunteerUser = {
  id: number;
  name: string;
  email?: string;
  contact_number?: string | null;
  barangay?: string | null;
  role: 'volunteer';
  must_change_password?: boolean;
};

export type MobileLoginResponse = {
  data: {
    token: string;
    must_change_password: boolean;
    user: VolunteerUser;
  };
};
