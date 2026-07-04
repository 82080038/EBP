"""
Complex Systems Approach for Financial Markets — CFA Institute (2025).

Based on CFA Institute Research Report (2025): "Reframing Financial Markets
as Complex Systems". Implements network theory, contagion analysis, and
agent-based modeling for portfolio risk management.

Key concepts:
- Correlation networks: Map relationships between assets
- Systemic risk nodes: Identify assets that can trigger cascading failures
- Contagion paths: Routes for shock propagation
- Agent-based simulation: Model heterogeneous investor behavior
- Network stress test: Topology-aware stress testing
"""

import pandas as pd
import numpy as np
from dataclasses import dataclass, field
from typing import Dict, List, Optional, Tuple
from collections import defaultdict


@dataclass
class NetworkNode:
    """Node in the correlation network."""
    ticker: str
    centrality: float = 0.0
    degree: int = 0
    cluster_id: int = 0
    systemic_risk_score: float = 0.0


@dataclass
class NetworkEdge:
    """Edge in the correlation network."""
    source: str
    target: str
    weight: float  # Correlation strength
    direction: str = "positive"  # "positive" or "negative"


@dataclass
class ContagionPath:
    """A contagion path from one asset to another."""
    source: str
    target: str
    path: List[str]
    total_weight: float
    path_length: int


@dataclass
class ComplexSystemsResult:
    """Complete complex systems analysis result."""
    nodes: List[NetworkNode] = field(default_factory=list)
    edges: List[NetworkEdge] = field(default_factory=list)
    clusters: Dict[int, List[str]] = field(default_factory=dict)
    systemic_nodes: List[str] = field(default_factory=list)
    contagion_risk: float = 0.0
    network_density: float = 0.0
    avg_clustering: float = 0.0
    stress_test_scenarios: Dict = field(default_factory=dict)
    recommendation: str = ""
    details: Dict = field(default_factory=dict)


def build_correlation_network(
    returns: pd.DataFrame,
    correlation_threshold: float = 0.3,
    method: str = "pearson",
) -> Tuple[List[NetworkNode], List[NetworkEdge], pd.DataFrame]:
    """
    Build a correlation network from asset returns.

    Nodes = assets, Edges = significant correlations.
    Uses threshold to filter weak correlations.

    Args:
        returns: DataFrame of asset returns (columns = tickers)
        correlation_threshold: Minimum |correlation| to create an edge
        method: "pearson", "spearman", or "kendall"

    Returns:
        (nodes, edges, correlation_matrix)
    """
    # Compute correlation matrix
    corr_matrix = returns.corr(method=method)

    # Build edges
    tickers = list(corr_matrix.columns)
    edges = []
    adjacency = defaultdict(list)

    for i in range(len(tickers)):
        for j in range(i + 1, len(tickers)):
            corr = corr_matrix.iloc[i, j]
            if pd.isna(corr):
                continue
            if abs(corr) >= correlation_threshold:
                edge = NetworkEdge(
                    source=tickers[i],
                    target=tickers[j],
                    weight=abs(corr),
                    direction="positive" if corr > 0 else "negative",
                )
                edges.append(edge)
                adjacency[tickers[i]].append(tickers[j])
                adjacency[tickers[j]].append(tickers[i])

    # Build nodes with centrality (degree centrality)
    n_assets = len(tickers)
    nodes = []
    for ticker in tickers:
        degree = len(adjacency.get(ticker, []))
        centrality = degree / max(n_assets - 1, 1)
        nodes.append(NetworkNode(
            ticker=ticker,
            centrality=centrality,
            degree=degree,
        ))

    return nodes, edges, corr_matrix


def detect_clusters(
    nodes: List[NetworkNode],
    edges: List[NetworkEdge],
    n_clusters: int = 3,
) -> Dict[int, List[str]]:
    """
    Detect clusters of tightly correlated assets using simple greedy modularity.

    Groups assets that are densely connected to each other.
    """
    # Build adjacency dict
    adjacency = defaultdict(set)
    for edge in edges:
        adjacency[edge.source].add(edge.target)
        adjacency[edge.target].add(edge.source)

    # Simple clustering: BFS-based community detection
    visited = set()
    clusters = {}
    cluster_id = 0

    for node in nodes:
        if node.ticker in visited:
            continue

        # BFS to find connected component
        cluster_members = []
        queue = [node.ticker]
        visited.add(node.ticker)

        while queue:
            current = queue.pop(0)
            cluster_members.append(current)
            for neighbor in adjacency.get(current, set()):
                if neighbor not in visited:
                    visited.add(neighbor)
                    queue.append(neighbor)

        clusters[cluster_id] = cluster_members
        cluster_id += 1

        if cluster_id >= n_clusters * 3:  # Limit clusters
            break

    # Assign cluster IDs to nodes
    node_map = {n.ticker: n for n in nodes}
    for cid, members in clusters.items():
        for ticker in members:
            if ticker in node_map:
                node_map[ticker].cluster_id = cid

    # Merge small clusters into larger ones
    large_clusters = {k: v for k, v in clusters.items() if len(v) >= 2}
    if len(large_clusters) > n_clusters:
        # Keep top n_clusters by size
        sorted_clusters = sorted(large_clusters.items(), key=lambda x: len(x[1]), reverse=True)
        large_clusters = dict(sorted_clusters[:n_clusters])

    return large_clusters


def systemic_risk_nodes(
    nodes: List[NetworkNode],
    edges: List[NetworkEdge],
    corr_matrix: pd.DataFrame,
) -> List[str]:
    """
    Identify systemic risk nodes — assets with highest centrality that can
    trigger cascading failures.

    Uses degree centrality + correlation strength.
    """
    # Compute weighted centrality
    weighted_degree = defaultdict(float)
    for edge in edges:
        weighted_degree[edge.source] += edge.weight
        weighted_degree[edge.target] += edge.weight

    # Normalize
    max_wd = max(weighted_degree.values()) if weighted_degree else 1

    for node in nodes:
        wd = weighted_degree.get(node.ticker, 0)
        node.systemic_risk_score = (wd / max_wd) * 0.6 + node.centrality * 0.4

    # Sort by systemic risk score
    sorted_nodes = sorted(nodes, key=lambda n: n.systemic_risk_score, reverse=True)
    systemic = [n.ticker for n in sorted_nodes[:5] if n.systemic_risk_score > 0.3]

    return systemic


def detect_contagion_paths(
    edges: List[NetworkEdge],
    source: str,
    max_depth: int = 3,
) -> List[ContagionPath]:
    """
    Detect contagion paths from a source asset to all other assets.
    Uses BFS to find shortest paths in the correlation network.
    """
    # Build adjacency
    adjacency = defaultdict(list)
    for edge in edges:
        adjacency[edge.source].append((edge.target, edge.weight))
        adjacency[edge.target].append((edge.source, edge.weight))

    # BFS
    paths = []
    visited = {source}
    queue = [(source, [source], 1.0)]

    while queue:
        current, path, weight = queue.pop(0)

        if len(path) > max_depth:
            continue

        for neighbor, edge_weight in adjacency.get(current, []):
            if neighbor in visited or neighbor in path:
                continue

            new_path = path + [neighbor]
            new_weight = weight * edge_weight

            if len(new_path) >= 2:
                paths.append(ContagionPath(
                    source=source,
                    target=neighbor,
                    path=new_path,
                    total_weight=new_weight,
                    path_length=len(new_path) - 1,
                ))

            visited.add(neighbor)
            queue.append((neighbor, new_path, new_weight))

    # Sort by weight (strongest contagion first)
    paths.sort(key=lambda p: p.total_weight, reverse=True)
    return paths[:20]  # Top 20 paths


def agent_based_simulation(
    returns: pd.DataFrame,
    n_agents: int = 100,
    n_steps: int = 252,
    agent_types: Optional[Dict[str, float]] = None,
) -> Dict:
    """
    Simple agent-based market simulation with heterogeneous investor behavior.

    Agent types:
    - fundamental: Buy when price < intrinsic value, sell when above
    - momentum: Buy when price rising, sell when falling
    - contrarian: Buy when price falling, sell when rising
    - noise: Random trades

    Args:
        agent_types: Dict of {type: proportion}. Default: 30% fundamental, 30% momentum, 20% contrarian, 20% noise

    Returns simulation results.
    """
    if agent_types is None:
        agent_types = {"fundamental": 0.3, "momentum": 0.3, "contrarian": 0.2, "noise": 0.2}

    tickers = list(returns.columns)
    n_tickers = len(tickers)

    # Initialize agent positions
    n_fund = int(n_agents * agent_types.get("fundamental", 0.3))
    n_mom = int(n_agents * agent_types.get("momentum", 0.3))
    n_con = int(n_agents * agent_types.get("contrarian", 0.2))
    n_noise = n_agents - n_fund - n_mom - n_con

    # Simulate
    price_impact = np.zeros((n_steps, n_tickers))
    returns.cumsum().values

    for step in range(min(n_steps, len(returns))):
        for t in range(n_tickers):
            ret = returns.iloc[step, t] if step < len(returns) else 0

            # Fundamental agents: mean reversion
            fund_impact = -ret * 0.5 * n_fund / n_agents

            # Momentum agents: follow trend
            mom_impact = ret * 0.3 * n_mom / n_agents

            # Contrarian agents: against trend
            con_impact = -ret * 0.4 * n_con / n_agents

            # Noise agents: random
            noise_impact = np.random.normal(0, 0.001) * n_noise / n_agents

            price_impact[step, t] = fund_impact + mom_impact + con_impact + noise_impact

    # Compute simulation metrics
    sim_returns = pd.DataFrame(price_impact, columns=tickers)
    sim_volatility = sim_returns.std()
    sim_correlation = sim_returns.corr()

    # Detect emergent phenomena
    avg_corr = sim_correlation.values[np.triu_indices(n_tickers, k=1)].mean()
    max_corr = sim_correlation.values[np.triu_indices(n_tickers, k=1)].max()

    return {
        "n_agents": n_agents,
        "agent_types": agent_types,
        "simulated_volatility": sim_volatility.to_dict(),
        "avg_correlation": float(avg_corr),
        "max_correlation": float(max_corr),
        "emergent_clustering": bool(avg_corr > 0.5),
        "contagion_detected": bool(max_corr > 0.7),
    }


def network_stress_test(
    nodes: List[NetworkNode],
    edges: List[NetworkEdge],
    corr_matrix: pd.DataFrame,
    systemic_nodes_list: List[str],
    shock_magnitude: float = -0.05,
) -> Dict:
    """
    Network-based stress test — simulate shock propagation from systemic nodes.

    Args:
        shock_magnitude: Initial shock (e.g., -5% = -0.05)
    """
    # Build adjacency with weights
    adjacency = defaultdict(dict)
    for edge in edges:
        adjacency[edge.source][edge.target] = edge.weight
        adjacency[edge.target][edge.source] = edge.weight

    # Propagate shock from each systemic node
    scenarios = {}
    tickers = list(corr_matrix.columns)

    for source in systemic_nodes_list:
        if source not in adjacency:
            continue

        # Initial shock
        impacts = {source: shock_magnitude}
        queue = [(source, shock_magnitude, 0)]
        visited = {source}

        while queue:
            current, impact, depth = queue.pop(0)
            if depth >= 3:  # Max propagation depth
                continue

            for neighbor, weight in adjacency.get(current, {}).items():
                if neighbor in visited:
                    continue
                # Propagate with decay
                propagated = impact * weight * 0.7  # Decay factor
                impacts[neighbor] = impacts.get(neighbor, 0) + propagated
                visited.add(neighbor)
                queue.append((neighbor, propagated, depth + 1))

        # Calculate portfolio impact (equal weight)
        total_impact = np.mean(list(impacts.values()))
        worst_impact = np.min(list(impacts.values()))
        affected_count = len(impacts)

        scenarios[source] = {
            "shock_source": source,
            "initial_shock": shock_magnitude,
            "affected_assets": affected_count,
            "avg_portfolio_impact": float(total_impact),
            "worst_asset_impact": float(worst_impact),
            "contagion_radius": affected_count / len(tickers),
        }

    # Overall network risk
    worst_scenario = max(scenarios.values(), key=lambda x: abs(x["avg_portfolio_impact"])) if scenarios else None

    return {
        "scenarios": scenarios,
        "worst_case": worst_scenario,
        "network_vulnerability": abs(worst_scenario["avg_portfolio_impact"]) if worst_scenario else 0,
        "n_systemic_nodes": len(systemic_nodes_list),
    }


def run_complex_systems_analysis(
    returns: pd.DataFrame,
    correlation_threshold: float = 0.3,
    shock_magnitude: float = -0.05,
) -> ComplexSystemsResult:
    """
    Run complete complex systems analysis on asset returns.

    Combines network construction, clustering, systemic risk detection,
    contagion analysis, and stress testing.
    """
    if returns.empty or returns.shape[1] < 2:
        return ComplexSystemsResult(details={"error": "Insufficient data"})

    # Build network
    nodes, edges, corr_matrix = build_correlation_network(
        returns, correlation_threshold=correlation_threshold,
    )

    # Detect clusters
    clusters = detect_clusters(nodes, edges)

    # Systemic risk nodes
    systemic = systemic_risk_nodes(nodes, edges, corr_matrix)

    # Network metrics
    n_assets = len(nodes)
    n_edges = len(edges)
    max_edges = n_assets * (n_assets - 1) / 2
    density = n_edges / max(max_edges, 1)

    # Average clustering coefficient (simplified)
    adjacency = defaultdict(set)
    for edge in edges:
        adjacency[edge.source].add(edge.target)
        adjacency[edge.target].add(edge.source)

    clustering_coeffs = []
    for node in nodes:
        neighbors = adjacency.get(node.ticker, set())
        if len(neighbors) < 2:
            continue
        # Count edges between neighbors
        neighbor_edges = 0
        for n1 in neighbors:
            for n2 in neighbors:
                if n1 < n2 and n2 in adjacency.get(n1, set()):
                    neighbor_edges += 1
        max_neighbor_edges = len(neighbors) * (len(neighbors) - 1) / 2
        cc = neighbor_edges / max(max_neighbor_edges, 1)
        clustering_coeffs.append(cc)

    avg_clustering = np.mean(clustering_coeffs) if clustering_coeffs else 0

    # Stress test
    stress = network_stress_test(nodes, edges, corr_matrix, systemic, shock_magnitude)

    # Contagion risk score
    contagion_risk = stress.get("network_vulnerability", 0)

    # Recommendation
    if contagion_risk > 0.03:
        rec = f"High network vulnerability ({contagion_risk:.1%}) — reduce exposure to systemic nodes: {', '.join(systemic[:3])}"
    elif contagion_risk > 0.01:
        rec = f"Moderate network risk — monitor systemic nodes: {', '.join(systemic[:3])}"
    else:
        rec = "Low network risk — portfolio is well diversified"

    return ComplexSystemsResult(
        nodes=nodes,
        edges=edges,
        clusters=clusters,
        systemic_nodes=systemic,
        contagion_risk=contagion_risk,
        network_density=density,
        avg_clustering=avg_clustering,
        stress_test_scenarios=stress,
        recommendation=rec,
    )
