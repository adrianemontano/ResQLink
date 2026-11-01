<?php

namespace Database\Seeders;

class MockDefenseIncidentData
{
    /**
     * Return 22 structured sample incidents per category for mock defense.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function get(): array
    {
        return [
            'Flood' => self::floods(),
            'Earthquake' => self::earthquakes(),
            'Landslide' => self::landslides(),
            'Fire' => self::fires(),
        ];
    }

    private static function floods(): array
    {
        return [
            ['barangay' => 'Mabolo', 'landmark' => 'MJ Cuenco Ave near Mabolo Church', 'lat' => 10.3182, 'lng' => 123.9165, 'status' => 'Reported', 'severity' => 'Low', 'notes' => 'Gutter-level pooling slowing tricycle passage.'],
            ['barangay' => 'Lahug', 'landmark' => 'Salinas Drive near JY Square', 'lat' => 10.3338, 'lng' => 123.8942, 'status' => 'Reported', 'severity' => 'Moderate', 'notes' => 'Creek overflow spilling onto two road lanes.'],
            ['barangay' => 'Tejero', 'landmark' => 'Tejero Creek near Blessed Sacrament', 'lat' => 10.3052, 'lng' => 123.9085, 'status' => 'Reported', 'severity' => 'High', 'notes' => 'Canal backflow entering ground-level residences.'],
            ['barangay' => 'Tinago', 'landmark' => 'Zulueta St near Barangay Hall', 'lat' => 10.3012, 'lng' => 123.9042, 'status' => 'Reported', 'severity' => 'High', 'notes' => 'High tide runoff inundating interior residential pathways.'],
            ['barangay' => 'Mambaling', 'landmark' => 'C. Padilla St near Alaska Mambaling', 'lat' => 10.2954, 'lng' => 123.8786, 'status' => 'Reported', 'severity' => 'Critical', 'notes' => 'Severe flood surge sweeping through coastal informal settlement.'],
            ['barangay' => 'Kasambagan', 'landmark' => 'F. Cabahug St near Subangdaku River', 'lat' => 10.3248, 'lng' => 123.9105, 'status' => 'Received', 'severity' => 'Low', 'notes' => 'Riverbank water level approaching curb height.'],
            ['barangay' => 'Sambag I', 'landmark' => 'Urgello St near SWU Compound', 'lat' => 10.3092, 'lng' => 123.8914, 'status' => 'Received', 'severity' => 'Moderate', 'notes' => 'Street flood trapping students in commercial corridors.'],
            ['barangay' => 'Basak San Nicolas', 'landmark' => 'N. Bacalso Ave near Shopwise area', 'lat' => 10.2982, 'lng' => 123.8685, 'status' => 'Received', 'severity' => 'Moderate', 'notes' => 'Drainage backlog causing impassable left lane.'],
            ['barangay' => 'Talamban', 'landmark' => 'Miñoza St near Talamban Gym', 'lat' => 10.3685, 'lng' => 123.9192, 'status' => 'Received', 'severity' => 'High', 'notes' => 'Mountain runoff cascading down main intersection.'],
            ['barangay' => 'Banilad', 'landmark' => 'Gov. Cuenco Ave near BTC', 'lat' => 10.3452, 'lng' => 123.9125, 'status' => 'Received', 'severity' => 'Critical', 'notes' => 'Flooding stranded several cars with water rising swiftly.'],
            ['barangay' => 'Tisa', 'landmark' => 'Katipunan St near Tisa Market', 'lat' => 10.3018, 'lng' => 123.8724, 'status' => 'Dispatched', 'severity' => 'Low', 'notes' => 'Creek rise causing ankle-deep water in marketplace.'],
            ['barangay' => 'Guadalupe', 'landmark' => 'V. Rama Ave near Guadalupe River', 'lat' => 10.3278, 'lng' => 123.8764, 'status' => 'Dispatched', 'severity' => 'Moderate', 'notes' => 'Guadalupe River level reached second warning alarm.'],
            ['barangay' => 'Capitol Site', 'landmark' => 'Escario St near Capitol Annex', 'lat' => 10.3172, 'lng' => 123.8915, 'status' => 'Dispatched', 'severity' => 'Moderate', 'notes' => 'Commercial parking and access road submerged.'],
            ['barangay' => 'Poblacion Pardo', 'landmark' => 'Pardo Public Market perimeter', 'lat' => 10.2922, 'lng' => 123.8582, 'status' => 'Dispatched', 'severity' => 'High', 'notes' => 'Market vendors evacuating produce as flood reaches stalls.'],
            ['barangay' => 'Punta Princesa', 'landmark' => 'F. Llamas St near Barangay Hall', 'lat' => 10.3022, 'lng' => 123.8824, 'status' => 'Dispatched', 'severity' => 'High', 'notes' => 'Severe roadway depression acting as catch basin.'],
            ['barangay' => 'Apas', 'landmark' => 'San Antonio Village IT Park boundary', 'lat' => 10.3392, 'lng' => 123.9074, 'status' => 'Dispatched', 'severity' => 'Critical', 'notes' => 'Overland runoff submerging houses along perimeter.'],
            ['barangay' => 'Luz', 'landmark' => 'Archbishop Reyes Ave near Luz Chapel', 'lat' => 10.3252, 'lng' => 123.9048, 'status' => 'Completed', 'severity' => 'Low', 'notes' => 'Drainage cleared and roadside waters subsided.'],
            ['barangay' => 'Pulangbato', 'landmark' => 'Pulangbato Bridge river crossing', 'lat' => 10.3832, 'lng' => 123.9184, 'status' => 'Completed', 'severity' => 'Low', 'notes' => 'River overflow receded without structural bridge damage.'],
            ['barangay' => 'Bacayan', 'landmark' => 'Bacayan Main Road near Riverview', 'lat' => 10.3725, 'lng' => 123.9240, 'status' => 'Completed', 'severity' => 'Moderate', 'notes' => 'Pumping team drained trapped rainwater from street.'],
            ['barangay' => 'Kinasang-an', 'landmark' => 'Kinasang-an Valley access road', 'lat' => 10.2885, 'lng' => 123.8610, 'status' => 'Completed', 'severity' => 'High', 'notes' => 'Rescue personnel evacuated 75 residents until waters cleared.'],
            ['barangay' => 'Bulacao', 'landmark' => 'Bulacao Creek boundary line', 'lat' => 10.2780, 'lng' => 123.8520, 'status' => 'Completed', 'severity' => 'Critical', 'notes' => 'Emergency dike reinforcement finished; families returned.'],
            ['barangay' => 'Suba', 'landmark' => 'Suba Fish Port perimeter road', 'lat' => 10.2940, 'lng' => 123.8950, 'status' => 'Completed', 'severity' => 'Critical', 'notes' => 'Coastal surge contained; post-flood cleaning completed.'],
        ];
    }

    private static function earthquakes(): array
    {
        return [
            ['barangay' => 'Capitol Site', 'landmark' => 'Capitol Building Complex plaza', 'lat' => 10.3168, 'lng' => 123.8912, 'status' => 'Reported', 'severity' => 'Low', 'notes' => 'Tremor felt on upper floors; minor wall cracks logged.'],
            ['barangay' => 'Guadalupe', 'landmark' => 'Guadalupe Parish Church grounds', 'lat' => 10.3280, 'lng' => 123.8755, 'status' => 'Reported', 'severity' => 'Moderate', 'notes' => 'Plaster peeled off external church belfry facade.'],
            ['barangay' => 'Banilad', 'landmark' => 'Commercial complex parking area', 'lat' => 10.3448, 'lng' => 123.9118, 'status' => 'Reported', 'severity' => 'High', 'notes' => 'Glass storefront panels shattered onto pedestrian walkway.'],
            ['barangay' => 'Lahug', 'landmark' => 'Salinas Drive commercial arcade', 'lat' => 10.3325, 'lng' => 123.8935, 'status' => 'Reported', 'severity' => 'High', 'notes' => 'Masonry parapet fell onto sidewalk; safety perimeter set.'],
            ['barangay' => 'Mabolo', 'landmark' => 'Juan Luna Ave office building', 'lat' => 10.3190, 'lng' => 123.9150, 'status' => 'Reported', 'severity' => 'Critical', 'notes' => 'Major column shear crack observed; building evacuated.'],
            ['barangay' => 'Sambag I', 'landmark' => 'Residential apartment block Urgello', 'lat' => 10.3085, 'lng' => 123.8905, 'status' => 'Received', 'severity' => 'Low', 'notes' => 'Superficial perimeter wall fracture reported.'],
            ['barangay' => 'Talamban', 'landmark' => 'Subdivision clubhouse entrance', 'lat' => 10.3675, 'lng' => 123.9180, 'status' => 'Received', 'severity' => 'Moderate', 'notes' => 'Decorative concrete arch cracked; cordoned off.'],
            ['barangay' => 'Tisa', 'landmark' => 'Tisa Elementary School grounds', 'lat' => 10.3010, 'lng' => 123.8715, 'status' => 'Received', 'severity' => 'Moderate', 'notes' => 'Gym floor joint shifted slightly during strong ground shake.'],
            ['barangay' => 'Kasambagan', 'landmark' => 'Golf course perimeter road', 'lat' => 10.3235, 'lng' => 123.9090, 'status' => 'Received', 'severity' => 'High', 'notes' => 'Boundary wall leaning outward toward public road.'],
            ['barangay' => 'Poblacion Pardo', 'landmark' => 'Old commercial building N. Bacalso', 'lat' => 10.2915, 'lng' => 123.8575, 'status' => 'Received', 'severity' => 'Critical', 'notes' => 'Structural failure on upper terrace threatening traffic below.'],
            ['barangay' => 'Punta Princesa', 'landmark' => 'Community Multi-Purpose Court', 'lat' => 10.3015, 'lng' => 123.8815, 'status' => 'Dispatched', 'severity' => 'Low', 'notes' => 'Public inspection of evacuation center after tremors.'],
            ['barangay' => 'Basak San Nicolas', 'landmark' => 'Residential compound Basak', 'lat' => 10.2975, 'lng' => 123.8675, 'status' => 'Dispatched', 'severity' => 'Moderate', 'notes' => 'Stairwell separation noticed in two-story lodging house.'],
            ['barangay' => 'Apas', 'landmark' => 'Military camp boundary road', 'lat' => 10.3385, 'lng' => 123.9065, 'status' => 'Dispatched', 'severity' => 'Moderate', 'notes' => 'Concrete perimeter section overturned; response team en route.'],
            ['barangay' => 'Luz', 'landmark' => 'Barangay Luz Gymnasium', 'lat' => 10.3245, 'lng' => 123.9038, 'status' => 'Dispatched', 'severity' => 'High', 'notes' => 'Ceiling truss alignment inspection underway.'],
            ['barangay' => 'Tejero', 'landmark' => 'Warehouse storage facility Tejero', 'lat' => 10.3045, 'lng' => 123.9075, 'status' => 'Dispatched', 'severity' => 'High', 'notes' => 'Industrial racks collapsed; first responders assisting workers.'],
            ['barangay' => 'Tinago', 'landmark' => 'Heritage residential townhouse', 'lat' => 10.3005, 'lng' => 123.9035, 'status' => 'Dispatched', 'severity' => 'Critical', 'notes' => 'Old brick wall collapsed inward; search team on scene.'],
            ['barangay' => 'Mambaling', 'landmark' => 'Flyover approach south coastal', 'lat' => 10.2945, 'lng' => 123.8775, 'status' => 'Completed', 'severity' => 'Low', 'notes' => 'Bridge joint inspection cleared by structural engineers.'],
            ['barangay' => 'Busay', 'landmark' => 'Busay viewpoint observation deck', 'lat' => 10.3575, 'lng' => 123.8675, 'status' => 'Completed', 'severity' => 'Low', 'notes' => 'Loose railing secured; deck reopened to public.'],
            ['barangay' => 'Kalunasan', 'landmark' => 'Kalunasan hillside community road', 'lat' => 10.3210, 'lng' => 123.8620, 'status' => 'Completed', 'severity' => 'Moderate', 'notes' => 'Road fissure patched with asphalt overlay.'],
            ['barangay' => 'Kamputhaw', 'landmark' => 'Gorordo Ave commercial tower', 'lat' => 10.3185, 'lng' => 123.8980, 'status' => 'Completed', 'severity' => 'High', 'notes' => 'Occupants evacuated, elevators reset, structure passed check.'],
            ['barangay' => 'Lorega San Miguel', 'landmark' => 'Public housing courtyard', 'lat' => 10.3060, 'lng' => 123.9010, 'status' => 'Completed', 'severity' => 'Critical', 'notes' => 'Evacuees provided emergency shelters; safety confirmed.'],
            ['barangay' => 'Zapatera', 'landmark' => 'Sikatuna St utility junction', 'lat' => 10.3075, 'lng' => 123.9040, 'status' => 'Completed', 'severity' => 'Critical', 'notes' => 'Unstable electrical pole replaced and power restored safely.'],
        ];
    }

    private static function landslides(): array
    {
        return [
            ['barangay' => 'Busay', 'landmark' => 'Transcentral Highway km 9 mountain bend', 'lat' => 10.3582, 'lng' => 123.8685, 'status' => 'Reported', 'severity' => 'Low', 'notes' => 'Small rocks and loose gravel scattered across highway.'],
            ['barangay' => 'Malubog', 'landmark' => 'Transcentral Highway km 18 junction', 'lat' => 10.3752, 'lng' => 123.8455, 'status' => 'Reported', 'severity' => 'Moderate', 'notes' => 'Shoulder soil slumped into outer drainage ditch.'],
            ['barangay' => 'Sirao', 'landmark' => 'Sirao Flower Farm upland route', 'lat' => 10.4285, 'lng' => 123.8655, 'status' => 'Reported', 'severity' => 'High', 'notes' => 'Mud and gravel slide trapping vehicle on steep grade.'],
            ['barangay' => 'Budlaan', 'landmark' => 'Budlaan Valley mountain footpath', 'lat' => 10.3792, 'lng' => 123.9025, 'status' => 'Reported', 'severity' => 'High', 'notes' => 'Embankment collapsed into pathway; foot traffic blocked.'],
            ['barangay' => 'Pulangbato', 'landmark' => 'Binaliw-Pulangbato slope boundary', 'lat' => 10.3835, 'lng' => 123.9188, 'status' => 'Reported', 'severity' => 'Critical', 'notes' => 'Massive earth collapse threatening 20 hillside houses.'],
            ['barangay' => 'Babag', 'landmark' => 'Babag Ridge upland trail', 'lat' => 10.3350, 'lng' => 123.8380, 'status' => 'Received', 'severity' => 'Low', 'notes' => 'Minor agricultural terrace runoff across access trail.'],
            ['barangay' => 'Binaliw', 'landmark' => 'Binaliw access road quarry sector', 'lat' => 10.3950, 'lng' => 123.9250, 'status' => 'Received', 'severity' => 'Moderate', 'notes' => 'Slope erosion deposited soil over access road.'],
            ['barangay' => 'Guba', 'landmark' => 'Guba mountain farm access', 'lat' => 10.4120, 'lng' => 123.8820, 'status' => 'Received', 'severity' => 'Moderate', 'notes' => 'Saturated earth slipped across barangay farm lane.'],
            ['barangay' => 'Lusaran', 'landmark' => 'Lusaran River canyon road', 'lat' => 10.4450, 'lng' => 123.8750, 'status' => 'Received', 'severity' => 'High', 'notes' => 'Rockfall partially blocking canyon river passage.'],
            ['barangay' => 'Mabini', 'landmark' => 'Mabini community access road', 'lat' => 10.4020, 'lng' => 123.8990, 'status' => 'Received', 'severity' => 'Critical', 'notes' => 'Severe slope failure severed mountain community road.'],
            ['barangay' => 'Agsungot', 'landmark' => 'Agsungot upland valley road', 'lat' => 10.3880, 'lng' => 123.9110, 'status' => 'Dispatched', 'severity' => 'Low', 'notes' => 'Clearance crew responding to minor mud wash.'],
            ['barangay' => 'Adlaon', 'landmark' => 'Adlaon farm road near creek', 'lat' => 10.4350, 'lng' => 123.8710, 'status' => 'Dispatched', 'severity' => 'Moderate', 'notes' => 'Fallen boulder obstructing one vehicle lane.'],
            ['barangay' => 'Paril', 'landmark' => 'Paril valley access crossing', 'lat' => 10.4250, 'lng' => 123.8950, 'status' => 'Dispatched', 'severity' => 'Moderate', 'notes' => 'Tree roots gave way causing earth movement on road.'],
            ['barangay' => 'Pung-ol Sibugay', 'landmark' => 'Mountain trail boundary', 'lat' => 10.3620, 'lng' => 123.8290, 'status' => 'Dispatched', 'severity' => 'High', 'notes' => 'Heavy soil detachment threatening uphill farm huts.'],
            ['barangay' => 'Sinsin', 'landmark' => 'Sinsin ridge access', 'lat' => 10.3150, 'lng' => 123.8150, 'status' => 'Dispatched', 'severity' => 'High', 'notes' => 'Backhoe dispatched to remove earth blockage.'],
            ['barangay' => 'Sudlon I', 'landmark' => 'Sudlon upland farm route', 'lat' => 10.3420, 'lng' => 123.8050, 'status' => 'Dispatched', 'severity' => 'Critical', 'notes' => 'Major landslide buried farm truck; rescue en route.'],
            ['barangay' => 'Sudlon II', 'landmark' => 'Sudlon II agricultural sector', 'lat' => 10.3550, 'lng' => 123.7950, 'status' => 'Completed', 'severity' => 'Low', 'notes' => 'Debris cleared from farm accessway.'],
            ['barangay' => 'Tagba-o', 'landmark' => 'Tagba-o creek slope', 'lat' => 10.4500, 'lng' => 123.8600, 'status' => 'Completed', 'severity' => 'Low', 'notes' => 'Minor gravel deposit cleared by community volunteers.'],
            ['barangay' => 'Taptap', 'landmark' => 'Transcentral Highway near Taptap Hall', 'lat' => 10.3980, 'lng' => 123.8320, 'status' => 'Completed', 'severity' => 'Moderate', 'notes' => 'Roadway reopened following barrier installation.'],
            ['barangay' => 'Toong', 'landmark' => 'Toong valley feeder road', 'lat' => 10.2990, 'lng' => 123.8380, 'status' => 'Completed', 'severity' => 'High', 'notes' => 'Retaining wall stabilized and slope tarpaulined.'],
            ['barangay' => 'Sapangdaku', 'landmark' => 'Sapangdaku riverbank cliff', 'lat' => 10.3310, 'lng' => 123.8550, 'status' => 'Completed', 'severity' => 'Critical', 'notes' => 'Evacuated 150 residents; engineering slope reinforced.'],
            ['barangay' => 'Kalunasan', 'landmark' => 'Upland subdivision boundary cliff', 'lat' => 10.3230, 'lng' => 123.8610, 'status' => 'Completed', 'severity' => 'Critical', 'notes' => 'Gabion installation completed; threat fully abated.'],
        ];
    }

    private static function fires(): array
    {
        return [
            ['barangay' => 'Basak San Nicolas', 'landmark' => 'C. Padilla St densely populated zone', 'lat' => 10.2985, 'lng' => 123.8682, 'status' => 'Reported', 'severity' => 'Low', 'notes' => 'Small trash fire near residential boundary under control.'],
            ['barangay' => 'Punta Princesa', 'landmark' => 'F. Llamas alleyway near chapel', 'lat' => 10.3025, 'lng' => 123.8822, 'status' => 'Reported', 'severity' => 'Moderate', 'notes' => 'Kitchen stove ignition caused smoke buildup in house.'],
            ['barangay' => 'Tisa', 'landmark' => 'Sitio Mansanas near Katipunan St', 'lat' => 10.3015, 'lng' => 123.8722, 'status' => 'Reported', 'severity' => 'High', 'notes' => 'Second alarm residential fire spreading to adjacent house.'],
            ['barangay' => 'Luz', 'landmark' => 'Sitio Zapatera alleyway near Luz', 'lat' => 10.3255, 'lng' => 123.9042, 'status' => 'Reported', 'severity' => 'High', 'notes' => 'Electrical fire on post sparking into shanty roofline.'],
            ['barangay' => 'Tejero', 'landmark' => 'Tejero interior pathway near dockyard', 'lat' => 10.3055, 'lng' => 123.9082, 'status' => 'Reported', 'severity' => 'Critical', 'notes' => 'Third alarm structural blaze in congested residential area.'],
            ['barangay' => 'Kasambagan', 'landmark' => 'Commercial restaurant kitchen Juan Luna', 'lat' => 10.3242, 'lng' => 123.9102, 'status' => 'Received', 'severity' => 'Low', 'notes' => 'Exhaust duct grease flareup contained by staff.'],
            ['barangay' => 'Mambaling', 'landmark' => 'Alaska Mambaling coastal community', 'lat' => 10.2952, 'lng' => 123.8782, 'status' => 'Received', 'severity' => 'Moderate', 'notes' => 'Light materials dwelling caught fire; neighbors suppressing.'],
            ['barangay' => 'Tinago', 'landmark' => 'Old commercial warehouse Zulueta', 'lat' => 10.3015, 'lng' => 123.9045, 'status' => 'Received', 'severity' => 'Moderate', 'notes' => 'Cardboard storage pile smoldering inside perimeter.'],
            ['barangay' => 'Sambag I', 'landmark' => 'Boarding house second floor Urgello', 'lat' => 10.3095, 'lng' => 123.8912, 'status' => 'Received', 'severity' => 'High', 'notes' => 'Appliance overheating ignited bedroom curtains.'],
            ['barangay' => 'Mabolo', 'landmark' => 'Auto repair garage MJ Cuenco', 'lat' => 10.3185, 'lng' => 123.9162, 'status' => 'Received', 'severity' => 'Critical', 'notes' => 'Chemical drum ignited; heavy smoke threatening homes.'],
            ['barangay' => 'Lahug', 'landmark' => 'Salinas Drive restaurant strip', 'lat' => 10.3342, 'lng' => 123.8945, 'status' => 'Dispatched', 'severity' => 'Low', 'notes' => 'Electrical breaker sparking; fire truck dispatched to stand by.'],
            ['barangay' => 'Capitol Site', 'landmark' => 'Escario St multi-tenant building', 'lat' => 10.3175, 'lng' => 123.8918, 'status' => 'Dispatched', 'severity' => 'Moderate', 'notes' => 'Basement generator smoke observed; fire crews en route.'],
            ['barangay' => 'Guadalupe', 'landmark' => 'Residential compound V. Rama', 'lat' => 10.3272, 'lng' => 123.8762, 'status' => 'Dispatched', 'severity' => 'Moderate', 'notes' => 'Two units affected; engine teams establishing water line.'],
            ['barangay' => 'Talamban', 'landmark' => 'Hardware supply warehouse Miñoza', 'lat' => 10.3682, 'lng' => 123.9195, 'status' => 'Dispatched', 'severity' => 'High', 'notes' => 'Lumber stock ignited; firemen actively fighting blaze.'],
            ['barangay' => 'Banilad', 'landmark' => 'Food stall cluster Gov. Cuenco', 'lat' => 10.3455, 'lng' => 123.9122, 'status' => 'Dispatched', 'severity' => 'High', 'notes' => 'Propane tank leakage ignited adjacent food kiosks.'],
            ['barangay' => 'Poblacion Pardo', 'landmark' => 'Pardo market perimeter store', 'lat' => 10.2925, 'lng' => 123.8585, 'status' => 'Dispatched', 'severity' => 'Critical', 'notes' => 'Multiple commercial stalls engulfed; mutual aid called.'],
            ['barangay' => 'Apas', 'landmark' => 'Residential compound near IT Park', 'lat' => 10.3395, 'lng' => 123.9072, 'status' => 'Completed', 'severity' => 'Low', 'notes' => 'Kitchen flame extinguished with handheld fire extinguisher.'],
            ['barangay' => 'Pasil', 'landmark' => 'L. Flores St fish market sector', 'lat' => 10.2910, 'lng' => 123.8920, 'status' => 'Completed', 'severity' => 'Low', 'notes' => 'Awning fire put out before reaching market roof.'],
            ['barangay' => 'Ermita', 'landmark' => 'Carbon market annex stalls', 'lat' => 10.2925, 'lng' => 123.8990, 'status' => 'Completed', 'severity' => 'Moderate', 'notes' => 'Short circuit fire suppressed; electrical wiring replaced.'],
            ['barangay' => 'Sawang Calero', 'landmark' => 'Tupas St coastal alleyway', 'lat' => 10.2930, 'lng' => 123.8850, 'status' => 'Completed', 'severity' => 'High', 'notes' => 'Two affected houses contained; fire declared out after 45m.'],
            ['barangay' => 'Duljo Fatima', 'landmark' => 'Spoliarium St interior area', 'lat' => 10.2935, 'lng' => 123.8730, 'status' => 'Completed', 'severity' => 'Critical', 'notes' => 'Fire out declared after 2h; relief goods distributed.'],
            ['barangay' => 'Suba', 'landmark' => 'Belgian St residential cluster', 'lat' => 10.2945, 'lng' => 123.8935, 'status' => 'Completed', 'severity' => 'Critical', 'notes' => 'Overhaul operations complete; 200 residents accounted for.'],
        ];
    }
}
