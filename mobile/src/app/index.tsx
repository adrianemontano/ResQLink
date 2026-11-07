import { StyleSheet, Text, View } from 'react-native';
import { SafeAreaView } from 'react-native-safe-area-context';

import { hasPublicConfiguration } from '@/config/environment';

export default function HomeScreen() {
  const isConfigured = hasPublicConfiguration();

  return (
    <SafeAreaView style={styles.safeArea}>
      <View style={styles.brandMark}>
        <Text style={styles.brandLetter}>R</Text>
      </View>
      <Text style={styles.title}>ResQLink</Text>
      <Text style={styles.subtitle}>Volunteer emergency reporting</Text>
      <View style={styles.statusCard}>
        <Text style={styles.statusTitle}>Mobile foundation ready</Text>
        <Text style={styles.statusText}>
          {isConfigured
            ? 'Local API and map configuration detected.'
            : 'Copy .env.example to .env.local and enter this laptop’s reachable LAN URLs.'}
        </Text>
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#0B1F33',
    paddingHorizontal: 24,
  },
  brandMark: {
    width: 72,
    height: 72,
    borderRadius: 20,
    backgroundColor: '#E23D3D',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 20,
  },
  brandLetter: {
    color: '#FFFFFF',
    fontSize: 38,
    fontWeight: '800',
  },
  title: {
    color: '#FFFFFF',
    fontSize: 34,
    fontWeight: '800',
  },
  subtitle: {
    color: '#B9C8D8',
    fontSize: 16,
    marginTop: 6,
  },
  statusCard: {
    width: '100%',
    maxWidth: 420,
    marginTop: 40,
    padding: 20,
    borderRadius: 16,
    backgroundColor: '#173652',
  },
  statusTitle: {
    color: '#FFFFFF',
    fontSize: 17,
    fontWeight: '700',
  },
  statusText: {
    color: '#D9E5F0',
    fontSize: 14,
    lineHeight: 21,
    marginTop: 8,
  },
});
